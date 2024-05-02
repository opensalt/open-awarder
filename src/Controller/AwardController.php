<?php

declare(strict_types=1);

namespace App\Controller;

use App\DataTable\Type\AwardDataTableType;
use App\Dto\MakeAward;
use App\Entity\Award;
use App\Entity\EmailTemplate;
use App\Entity\EvidenceFile;
use App\Entity\Participant;
use App\Enums\AwardState;
use App\Form\AwardType;
use App\Form\MakeAwardForm;
use App\Message\CheckIfAwardPublished;
use App\Message\PublishAward;
use App\Message\RevokeAward;
use App\Repository\AwardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use function Symfony\Component\String\u;

#[Route('/award')]
class AwardController extends AbstractController
{
    use DataTableFactoryAwareTrait;

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
    public function new(Request $request, EntityManagerInterface $entityManager, Environment $twig): Response
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

            $awarder = $form->get('awarder')->getData();
            $achievement = $form->get('achievement')->getData();
            $subject = $form->get('subject')->getData();
            $assertionId = Uuid::v7();
            $clrId = Uuid::v5(Uuid::fromString('018e5209-5518-757b-8cc1-6fb5f378a7ff'), $assertionId->toRfc4122());

            $credentials = $entityManager->getRepository(Participant::class)->getAchievementsForParticipant($subject);
            $credentials[] = $achievement->getIdentifier();

            $templateErrored = false;
            try {
                $context = [
                    'issuedOn' => new \DateTimeImmutable(),
                    'assertionId' => 'urn:uuid:' . $assertionId->toRfc4122(),
                    'clrId' => 'urn:uuid:' . $clrId->toRfc4122(),
                    'requestIdentity' => Uuid::v7()->toRfc4122(),
                    'pathway' => $subject->getSubscribedPathway()->getEmailTemplate(),
                    'credentialIds' => $credentials,
                ];
                $vars = $form->get('vars')->getData();
                $templateVars = array_merge($vars, ['awarder' => $awarder, 'achievement' => $achievement, 'subject' => $subject, 'context' => $context]);

                $awardTemplate = $award->awardTemplate->getTemplate();
                $resultDescriptions = ($achievement->getDefinition() ?? [])['resultDescriptions'] ?? [];
                if (null !== ($resultDescriptions[0]['name'] ?? null)) {
                    $awardTemplate['clr']['assertions'][0]['results'] = [];

                    foreach ($resultDescriptions as $resultDescription) {
                        if (null !== ($resultDescription['name'] ?? null)) {
                            $awardTemplate['clr']['assertions'][0]['results'][] = [
                                'resultDescription' => $resultDescription['id'],
                                'value' => '{{ ' . u($resultDescription['name'])->camel()->title()->toString() . ' }}',
                            ];
                        }
                    }
                }

                $template = $twig->createTemplate(preg_replace('/("~|~")/', '', json_encode($awardTemplate, JSON_THROW_ON_ERROR)));
                $renderedTemplate = $template->render($templateVars);
                // 2nd pass to replace variables that had variables in their content
                $template = $twig->createTemplate(preg_replace('/("~|~")/', '', $renderedTemplate));
                $awardTemplate = json_decode($template->render($templateVars), true);

                $emailTemplate = null;
                if ($award->emailTemplate instanceof EmailTemplate) {
                    $template = $twig->createTemplate($award->emailTemplate->getTemplate());
                    $emailTemplate = $template->render($templateVars);
                    // 2nd pass to replace variables that had variables in their content
                    $template = $twig->createTemplate($emailTemplate);
                    $emailTemplate = $template->render($templateVars);
                }
            } catch (\Throwable $e) {
                $form->addError(new FormError(message: $e->getMessage(), cause: $e));
                $templateErrored = true;
            }

            if (!$templateErrored) {
                $awarded = new Award($assertionId);
                $awarded->setAwarder($award->awarder);
                $awarded->setSubject($award->subject);
                $awarded->setAchievement($award->achievement);
                $awarded->setAwardTemplate($award->awardTemplate);
                $awarded->setEmailTemplate($award->emailTemplate);
                $awarded->setAwardJson($awardTemplate);
                $awarded->setAwardEmail($emailTemplate);
                $awarded->setAwardEmailFrom($award->emailTemplate?->getFrom());
                $awarded->setAwardEmailSubject($award->emailTemplate?->getSubject());
                $awarded->setState(AwardState::Pending);

                $awardEvidence = $form->get('evidence')->getData() ?? [];
                foreach ($awardEvidence as $evidence) {
                    $evidenceFile = new EvidenceFile();
                    $evidenceFile->setFile($evidence);
                    $evidenceFile->setAward($awarded);
                    $entityManager->persist($evidenceFile);

                    $awarded->addEvidence($evidenceFile);
                }

                $entityManager->persist($awarded);
                $entityManager->flush();

                return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
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
        if ($award->getState() !== AwardState::Pending) {
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

            $bus->dispatch(new PublishAward($award->getId()));
        }

        return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/updateStatus', name: 'app_award_status', methods: ['GET'])]
    public function status(Award $award, MessageBusInterface $bus, EntityManagerInterface $entityManager): Response
    {
        $bus->dispatch(new CheckIfAwardPublished($award->getId()));

        return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
    }
}
