<?php

declare(strict_types=1);

namespace App\Controller;

use App\DataTable\Type\AwardDataTableType;
use App\Dto\MakeAward;
use App\Entity\AchievementDefinition;
use App\Entity\Award;
use App\Entity\Awarder;
use App\Entity\EmailTemplate;
use App\Entity\EvidenceFile;
use App\Entity\Participant;
use App\Enums\AwardState;
use App\Form\AwardType;
use App\Form\MakeAwardForm;
use App\Message\Command\CheckIfAwardPublished;
use App\Message\Command\PublishAward;
use App\Message\Command\RevokeAward;
use App\Repository\AchievementDefinitionRepository;
use App\Repository\AwarderRepository;
use App\Repository\AwardRepository;
use App\Repository\AwardTemplateRepository;
use App\Repository\EmailTemplateRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use function Symfony\Component\String\u;

#[Route('/award')]
#[IsGranted('ROLE_ADMIN')]
class AwardController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParticipantRepository $participantRepository,
        private readonly AwarderRepository $awarderRepository,
        private readonly AchievementDefinitionRepository $achievementRepository,
        private readonly AwardTemplateRepository $awardTemplateRepository,
        private readonly EmailTemplateRepository $emailTemplateRepository,
        private readonly Environment $twig,
    ) {
    }

    #[Route('/', name: 'app_award_index', methods: ['GET'])]
    public function index(Request $request, AwardRepository $awardRepository): Response
    {
        $dataTable = $this->createDataTable(AwardDataTableType::class);
        $dataTable->handleRequest($request);

        return $this->render('award/index.html.twig', [
            //'awards' => $awardRepository->findBy([], ['id' => 'ASC']),
            'tableAwards' => $dataTable->createView(),
        ]);
    }

    #[Route('/new', name: 'app_award_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $award = new MakeAward();
        $form = $this->createForm(MakeAwardForm::class, $award, [
            'action' => $this->generateUrl('app_award_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var SubmitButton $submitButton */
                $submitButton = $form->get('submit');
            } catch (\Throwable) {
                $submitButton = null;
            }

            if (!$submitButton?->isClicked()) {
                return $this->render('award/new.html.twig', [
                    'award' => $award,
                    'form' => $form,
                ]);
            }

            try {
                $this->createAward($award);

                return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $e) {
                $form->addError(new FormError(message: $e->getMessage(), cause: $e));
            }
        }

        return $this->render('award/new.html.twig', [
            'award' => $award,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_award_show', methods: ['GET'])]
    public function show(Award $award): Response
    {
        return $this->render('award/show.html.twig', [
            'award' => $award,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_award_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Award $award, EntityManagerInterface $entityManager): Response
    {
        if ($award->getState() !== AwardState::Pending) {
            return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(AwardType::class, $award);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $deleteEvidence = $form->get('deleteFiles')->getData() ?? [];
            $evidenceFiles = $award->getEvidence();
            foreach ($deleteEvidence as $evidenceId) {
                $evidenceFile = $entityManager->getRepository(EvidenceFile::class)->find($evidenceId);
                if (null === $evidenceFile) {
                    continue;
                }

                if ($evidenceFiles->contains($evidenceFile)) {
                    $award->removeEvidence($evidenceFile);
                    $entityManager->remove($evidenceFile);
                }
            }

            $awardEvidence = $form->get('moreEvidence')->getData() ?? [];
            foreach ($awardEvidence as $evidence) {
                $evidenceFile = new EvidenceFile();
                $evidenceFile->setFile($evidence);
                $evidenceFile->setAward($award);
                $entityManager->persist($evidenceFile);

                $award->addEvidence($evidenceFile);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('award/edit.html.twig', [
            'award' => $award,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_award_delete', methods: ['POST'])]
    public function delete(Request $request, Award $award, EntityManagerInterface $entityManager): Response
    {
        if (!$award->canDelete()) {
            return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$award->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($award);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/revoke', name: 'app_award_revoke', methods: ['POST'])]
    public function revoke(Request $request, Award $award, MessageBusInterface $bus, EntityManagerInterface $entityManager): Response
    {
        if (in_array($award->getState(), [AwardState::Pending, AwardState::Revoking, AwardState::Revoked], true)) {
            return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('revoke'.$award->getId(), $request->getPayload()->get('_token'))) {
            $bus->dispatch(new RevokeAward($award->getId()));
            $award->setState(AwardState::Revoking);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/publish', name: 'app_award_publish', methods: ['POST'])]
    public function publish(Request $request, Award $award, MessageBusInterface $bus, EntityManagerInterface $entityManager): Response
    {
        if ($award->getState() !== AwardState::Pending) {
            return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('publish'.$award->getId(), $request->getPayload()->get('_token'))) {
            $award->setState(AwardState::Publishing);
            $entityManager->flush();

            $bus->dispatch(new PublishAward($award->getId()), [
                DelayStamp::delayFor(\DateInterval::createFromDateString('5 seconds')),
            ]);
        }

        return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/updateStatus', name: 'app_award_status', methods: ['GET'])]
    public function status(Award $award, MessageBusInterface $bus, EntityManagerInterface $entityManager): Response
    {
        $bus->dispatch(new CheckIfAwardPublished($award->getId()));

        return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/import', name: 'app_award_import', methods: ['GET', 'POST'], priority: 10)]
    public function import(Request $request): Response
    {
        $uploadForm = $this->createFormBuilder()
            ->add('file', FileType::class, [
            ])
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $uploadForm->handleRequest($request);

            try {
                if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
                    $file = $uploadForm->get('file')->getData();

                    if ($file) {
                        $reader = Reader::createFromPath($file->getPathname());
                        $reader->setHeaderOffset(0);
                        $awards = $reader->getRecords();

                        $row = 0;
                        foreach ($awards as $rec) {
                            $row++;
                            try {
                                $awarder = $this->awarderRepository->getAwarderFromName($rec['awarder']);
                                if (null === $awarder) {
                                    throw new \ErrorException(message: "Row {$row}: Awarder `{$rec['awarder']}` not found.", code: 0);
                                }
                                $achievement = $this->achievementRepository->getAchievementDefinitionFromName($rec['achievement']);
                                if (null === $achievement) {
                                    throw new \ErrorException(message: "Row {$row}: Achievement `{$rec['achievement']}` not found.", code: 0);
                                }
                                $subject = $this->participantRepository->getParticipantFromEmail($rec['participant']);
                                if (null === $subject) {
                                    throw new \ErrorException(message: "Row {$row}: Participant `{$rec['participant']}` not found.", code: 0);
                                }
                                $awardTemplate = $this->awardTemplateRepository->getTemplateFromName($rec['awardTemplate']);
                                if (null === $awardTemplate) {
                                    throw new \ErrorException(message: "Row {$row}: Award template `{$rec['awardTemplate']}` not found.", code: 0);
                                }
                                $emailTemplate = $this->emailTemplateRepository->getTemplateFromName($rec['emailTemplate']);
                                if (null === $emailTemplate) {
                                    throw new \ErrorException(message: "Row {$row}: Email template `{$rec['emailTemplate']}` not found.", code: 0);
                                }

                                $award = new MakeAward();
                                $award->awarder = $awarder;
                                $award->subject = $subject;
                                $award->achievement = $achievement;
                                $award->awardTemplate = $awardTemplate;
                                $award->emailTemplate = $emailTemplate;
                                unset($rec['awarder'], $rec['achievement'], $rec['subject'], $rec['awardTemplate'], $rec['emailTemplate']);
                                $award->vars = $rec;

                                $this->createAward($award);
                            } catch (\Throwable $e) {
                                if (str_contains($e->getMessage(), 'Undefined array key')) {
                                    throw new \ErrorException(
                                        message: (preg_replace('/.*"([^"]+)"/', '$1', $e->getMessage()).' column missing.'),
                                        code: $e->getCode(),
                                        previous: $e
                                    );
                                }

                                if (str_contains($e->getMessage(), 'does not exist in')) {
                                    throw new \ErrorException(
                                        message: (preg_replace('/[^"]*"([^"]+)".*/', '$1', $e->getMessage()).' column missing for award or email template.'),
                                        code: $e->getCode(),
                                        previous: $e
                                    );
                                }

                                throw new \ErrorException(message: "Row {$row}: ".$e->getMessage(), code: $e->getCode(), previous: $e);
                            }
                        }
                    }

                    return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
                }
            } catch (\Throwable $e) {
                $uploadForm->get('file')->addError(new FormError(message: 'Upload failed: '.$e->getMessage()));

                return $this->render('award/import.html.twig', [
                    'uploadForm' => $uploadForm->createView(),
                ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
            }
        }

        return $this->render('award/import.html.twig', [
            'uploadForm' => $uploadForm->createView(),
        ]);
    }

    private function useClr1Definition(array $achievementDefinition): array
    {
        unset($achievementDefinition['type']);

        if (null !== ($achievementDefinition['image'] ?? null) && null !== ($achievementDefinition['image']['id'] ?? null)) {
            $achievementDefinition['image'] = $achievementDefinition['image']['id'];
        }

        if (null === ($achievementDefinition['issuer'] ?? null)) {
            // OB3 to CLR1 difference
            $achievementDefinition['issuer'] = [
                'id' => 'urn:uuid:{{ awarder.id }}',
                'name' => '{{ awarder.name }}',
            ];
        }

        if (null !== ($achievementDefinition['criteria'] ?? null)) {
            // OB3 to CLR1 difference
            $achievementDefinition['requirement'] = $achievementDefinition['criteria'];
            unset($achievementDefinition['criteria']);
        }

        if (null !== ($achievementDefinition['resultDescription'] ?? null)) {
            // OB3 to CLR1 difference
            $achievementDefinition['resultDescriptions'] = $achievementDefinition['resultDescription'];
            unset($achievementDefinition['resultDescriptions']);
        }

        if (null !== ($achievementDefinition['alignment'] ?? null)) {
            // OB3 to CLR1 difference
            $achievementDefinition['alignments'] = $achievementDefinition['alignment'];
            unset($achievementDefinition['alignment']);
        }

        return $achievementDefinition;
    }

    private function fixupDefinition(array $achievementDefinition, ?int $clrType): array
    {
        // @context is not needed in the CLR achievement as it is already included in the outer template
        if (null !== ($achievementDefinition['@context'] ?? null)) {
            unset($achievementDefinition['@context']);
        }

        // achievementType should be a single string, fix if it is an array
        if (is_array($achievementDefinition['achievementType'] ?? null)) {
            $achievementDefinition['achievementType'] = $achievementDefinition['achievementType'][0];
        }

        // Convert to CLR1 if needed, otherwise CLR2
        if (1 === $clrType) {
            $achievementDefinition = $this->useClr1Definition($achievementDefinition);
        }

        return $achievementDefinition;
    }

    private function determineClrVersion($clr): ?int
    {
        if (null !== ($clr['assertions'] ?? null)) {
            return 1;
        }

        if (null !== ($clr['credentialSubject'] ?? null)) {
            return 2;
        }

        return null;
    }

    private function saveNewAward(Uuid $assertionId, MakeAward $award, array $awardJson, ?string $awardEmail, array $awardEvidence): void
    {
        $awarded = new Award($assertionId);
        $awarded->setAwarder($award->awarder);
        $awarded->setSubject($award->subject);
        $awarded->setAchievement($award->achievement);
        $awarded->setAwardTemplate($award->awardTemplate);
        $awarded->setEmailTemplate($award->emailTemplate);
        $awarded->setAwardJson($awardJson);
        $awarded->setAwardEmail($awardEmail);
        $awarded->setAwardEmailFrom($award->emailTemplate?->getFrom());
        $awarded->setAwardEmailSubject($award->emailTemplate?->getSubject());
        $awarded->setState(AwardState::Pending);

        foreach ($awardEvidence as $evidence) {
            $evidenceFile = new EvidenceFile();
            $evidenceFile->setFile($evidence);
            $evidenceFile->setAward($awarded);
            $this->entityManager->persist($evidenceFile);

            $awarded->addEvidence($evidenceFile);
        }

        $this->entityManager->persist($awarded);
        $this->entityManager->flush();
    }

    private function renderAwardTemplate(array $awardTemplate, array $templateVars): array
    {
        $template = $this->twig->createTemplate(preg_replace('/("~|~")/', '', json_encode($awardTemplate, JSON_THROW_ON_ERROR)));
        $renderedTemplate = $template->render($templateVars);

        // 2nd pass to replace variables that had variables in their content
        $template = $this->twig->createTemplate(preg_replace('/("~|~")/', '', $renderedTemplate));

        return json_decode($template->render($templateVars), true);
    }

    private function renderEmailTemplate(MakeAward $award, array $templateVars): ?string
    {
        if ($award->emailTemplate instanceof EmailTemplate) {
            $template = $this->twig->createTemplate($award->emailTemplate->getTemplate());
            $emailTemplate = $template->render($templateVars);
            // 2nd pass to replace variables that had variables in their content
            $template = $this->twig->createTemplate($emailTemplate);
            $emailTemplate = $template->render($templateVars);
        }

        return $emailTemplate ?? null;
    }

    private function addResults(array $achievementDefinition, array $awardTemplate): ?array
    {
        $clrType = $this->determineClrVersion($awardTemplate['clr']);

        $results = [];
        $resultDescriptions = $achievementDefinition['resultDescription'] ?? $achievementDefinition['resultDescriptions'] ?? [];
        foreach ($resultDescriptions as $resultDescription) {
            if ((null !== ($resultDescription['name'] ?? null)) && (null !== ($resultDescription['id'] ?? null))) {
                $results[] = [
                    'resultDescription' => $resultDescription['id'],
                    'value' => '{{ ' . u($resultDescription['name'])->camel()->title()->toString() . ' }}',
                ];
            }
        }

        if ($results !== []) {
            switch ($clrType) {
                case 1:
                    $awardTemplate['clr']['assertions'][0]['results'] = $results;
                    break;
                case 2:
                    $awardTemplate['clr']['credentialSubject']['verifiableCredential'][0]['credentialSubject']['result'] = $results;
                    break;
            }
        }

        return $awardTemplate;
    }

    private function createAward(MakeAward $award): void
    {
        $awarder = $award->awarder;
        $achievement = clone $award->achievement;
        $subject = $award->subject;
        $vars = $award->vars;
        $awardEvidence = $award->evidence ?? [];

        $assertionId = Uuid::v7();
        $clrId = Uuid::v5(Uuid::fromString('018e5209-5518-757b-8cc1-6fb5f378a7ff'), $assertionId->toRfc4122());

        $credentialIds = $this->participantRepository->getAchievementsForParticipant($subject);
        $credentialIds[] = $achievement->getIdentifier();

        $context = [
            'issuedOn' => new \DateTimeImmutable(),
            'assertionId' => 'urn:uuid:' . $assertionId->toRfc4122(),
            'clrId' => 'urn:uuid:' . $clrId->toRfc4122(),
            'requestIdentity' => Uuid::v7()->toRfc4122(),
            'pathway' => $subject->getSubscribedPathway()->getName(),
            'pathwayEmailTemplate' => $subject->getSubscribedPathway()->getEmailTemplate(),
            'pathwayFinalCredential' => $subject->getSubscribedPathway()->getFinalCredential()->getIdentifier(),
            'credentialIds' => $credentialIds,
        ];

        $awardTemplate = $award->awardTemplate->getTemplate();
        $clrType = $this->determineClrVersion($awardTemplate['clr']);

        $achievementDefinition = $achievement->getDefinition() ?? [];
        $achievementDefinition = $this->fixupDefinition($achievementDefinition, $clrType);
        $achievement->setDefinition($achievementDefinition);

        $awardTemplate = $this->addResults($achievementDefinition, $awardTemplate);

        $templateVars = array_merge($vars, ['awarder' => $awarder, 'achievement' => $achievement, 'subject' => $subject, 'context' => $context]);
        $awardTemplate = $this->renderAwardTemplate($awardTemplate, $templateVars);
        $emailTemplate = $this->renderEmailTemplate($award, $templateVars);

        $this->saveNewAward($assertionId, $award, $awardTemplate, $emailTemplate, $awardEvidence);
    }
}
