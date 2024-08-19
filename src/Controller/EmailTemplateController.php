<?php

declare(strict_types=1);

namespace App\Controller;

use App\DataTable\Type\EmailTemplateDataTableType;
use App\Dto\SendFromTemplate;
use App\Dto\TemplatePreview;
use App\Entity\AchievementDefinition;
use App\Entity\Awarder;
use App\Entity\Email;
use App\Entity\EmailAttachment;
use App\Entity\EmailTemplate;
use App\Entity\Participant;
use App\Enums\EmailState;
use App\Form\EmailTemplateType;
use App\Form\SendFromTemplateType;
use App\Form\TemplatePreviewType;
use App\Message\Command\SendEmail;
use App\Repository\ParticipantRepository;
use App\Service\TwigVariables;
use Doctrine\ORM\EntityManagerInterface;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use Vich\UploaderBundle\Storage\StorageInterface;

#[Route('/template/email')]
#[IsGranted('ROLE_ADMIN')]
class EmailTemplateController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public function __construct(
        private readonly Environment $twig,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'app_email_template_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $dataTable = $this->createDataTable(EmailTemplateDataTableType::class);
        $dataTable->handleRequest($request);

        return $this->render('email_template/index.html.twig', [
            'table' => $dataTable->createView(),
        ]);
    }

    #[Route('/new', name: 'app_email_template_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TwigVariables $twigVariables): Response
    {
        $emailTemplate = new EmailTemplate();
        $form = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $twigVariables->getVariables($emailTemplate->getTemplate());
                $entityManager->persist($emailTemplate);

                $emailAttachments = $form->get('attachments')->getData() ?? [];
                foreach ($emailAttachments as $attachment) {
                    $evidenceFile = new EmailAttachment();
                    $evidenceFile->setFile($attachment);
                    $evidenceFile->setTemplate($emailTemplate);
                    $entityManager->persist($evidenceFile);

                    $emailTemplate->addAttachment($evidenceFile);
                }

                $entityManager->flush();

                return $this->redirectToRoute('app_email_template_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $throwable) {
                $form->addError(new FormError(message: $throwable->getMessage(), cause: $throwable));
            }
        }

        return $this->render('email_template/new.html.twig', [
            'email_template' => $emailTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_email_template_show', methods: ['GET'])]
    public function show(EmailTemplate $emailTemplate): Response
    {
        return $this->render('email_template/show.html.twig', [
            'email_template' => $emailTemplate,
        ]);
    }

    #[Route('/{id:emailTemplate}/send', name: 'app_email_template_send', methods: ['GET', 'POST'])]
    public function send(Request $request, EmailTemplate $emailTemplate, MessageBusInterface $bus): Response
    {
        $template = new SendFromTemplate();
        $template->emailTemplate = $emailTemplate;
        $form = $this->createForm(SendFromTemplateType::class, $template);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $content = $this->generateContent($emailTemplate, $template->participant, $template->achievement, $template->awarder);

                $email = new Email(EmailState::Ready);
                $email->setFromEmailTemplate($emailTemplate);
                $email->setSubject($template->participant);
                $email->setRenderedEmail($content);

                $this->entityManager->persist($email);
                $this->entityManager->flush();

                $bus->dispatch(new SendEmail($email->getId()), [
                    DelayStamp::delayFor(\DateInterval::createFromDateString('5 seconds'))
                ]);

                return $this->redirectToRoute('app_email_index');
            }
        } catch (\Throwable $throwable) {
            $form->addError(new FormError(message: $throwable->getMessage(), cause: $throwable));
        }

        return $this->render('email_template/send.html.twig', [
            'form' => $form,
            'email_template' => $emailTemplate,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_email_template_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EmailTemplate $emailTemplate, EntityManagerInterface $entityManager, TwigVariables $twigVariables): Response
    {
        $form = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $twigVariables->getVariables($emailTemplate->getTemplate());
                $deleteAttachments = $form->get('deleteFiles')->getData() ?? [];
                $attachments = $emailTemplate->getAttachments();
                foreach ($deleteAttachments as $evidenceId) {
                    $evidenceFile = $entityManager->getRepository(EmailAttachment::class)->find($evidenceId);
                    if (null === $evidenceFile) {
                        continue;
                    }

                    if ($attachments->contains($evidenceFile)) {
                        $emailTemplate->removeAttachment($evidenceFile);
                        $entityManager->remove($evidenceFile);
                    }
                }

                $emailAttachments = $form->get('attachments')->getData() ?? [];
                foreach ($emailAttachments as $attachment) {
                    $evidenceFile = new EmailAttachment();
                    $evidenceFile->setFile($attachment);
                    $evidenceFile->setTemplate($emailTemplate);
                    $entityManager->persist($evidenceFile);

                    $emailTemplate->addAttachment($evidenceFile);
                }

                $entityManager->flush();

                /* @phpstan-ignore method.notFound */
                if ($form->get('saveAndContinue')->isClicked()) {
                    return $this->redirectToRoute('app_email_template_edit', ['id' => $emailTemplate->getId()], Response::HTTP_SEE_OTHER);
                }

                return $this->redirectToRoute('app_email_template_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $throwable) {
                $form->addError(new FormError(message: $throwable->getMessage(), cause: $throwable));
            }
        }

        return $this->render('email_template/edit.html.twig', [
            'email_template' => $emailTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/duplicate', name: 'app_email_template_duplicate', methods: ['POST'])]
    public function duplicate(Request $request, EmailTemplate $emailTemplate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('duplicate'.$emailTemplate->getId(), $request->getPayload()->get('_token'))) {
            $copy = new EmailTemplate();
            $copy->setName($emailTemplate->getName().' - Copy '.\uniqid(''));
            $copy->setFrom($emailTemplate->getFrom());
            $copy->setSubject($emailTemplate->getSubject());
            $copy->setTemplate($emailTemplate->getTemplate());
            $copy->setFields($emailTemplate->getFields());
            foreach ($emailTemplate->getAwarders() as $awarder) {
                $copy->addAwarder($awarder);
                $awarder->addEmailTemplate($copy);
            }

            foreach ($emailTemplate->getAttachments() as $attachment) {
                $copyAttachment = new EmailAttachment();
                $copyAttachment->setTemplate($copy);
                $copyAttachment->setFile($attachment->getFile());
                $copyAttachment->setName($attachment->getName());
                $copyAttachment->setSize($attachment->getSize());
                $copyAttachment->setMimetype($attachment->getMimetype());
                $copyAttachment->setOriginalName($attachment->getOriginalName());
                $copyAttachment->setDimensions($attachment->getDimensions());

                $copy->addAttachment($copyAttachment);
                $entityManager->persist($copyAttachment);
            }

            $entityManager->persist($copy);

            $entityManager->flush();

            return $this->redirectToRoute('app_email_template_edit', ['id' => $copy->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_email_template_show', ['id' => $emailTemplate->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_email_template_delete', methods: ['POST'])]
    public function delete(Request $request, EmailTemplate $emailTemplate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$emailTemplate->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($emailTemplate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_email_template_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/preview', name: 'app_email_template_preview_setup', methods: ['GET'])]
    public function previewSetup(Request $request, EmailTemplate $emailTemplate, Environment $twig, EntityManagerInterface $entityManager): Response
    {
        $template = new TemplatePreview();
        $template->emailTemplate = $emailTemplate;
        $form = $this->createForm(TemplatePreviewType::class, $template);
        $form->handleRequest($request);

        return $this->render('email_template/preview.html.twig', [
            'form' => $form,
            'email_template' => $emailTemplate,
        ]);
    }

    #[Route('/{id:emailTemplate}/preview/{awarder}/{achievement}/{subject}', name: 'app_email_template_preview', methods: ['GET'])]
    public function preview(
        EmailTemplate $emailTemplate,
        ?Awarder $awarder,
        ?AchievementDefinition $achievement,
        ?Participant $subject,
    ): Response
    {
        $response = new Response();

        try {
            $content = $this->generateContent($emailTemplate, $subject, $achievement, $awarder);
            $content = preg_replace('#\bcid:#', 'preview/', $content);

            $response->setContent($content);
        } catch (\Throwable $throwable) {
            $response = new Response($throwable->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $response;
    }

    #[Route('/{id:emailTemplate}/preview/{awarder}/{achievement}/{participant}/{filename}', name: 'app_email_template_preview_file', methods: ['GET'])]
    public function previewFile(EmailTemplate $emailTemplate, string $filename, StorageInterface $storage, FilesystemOperator $evidenceStorage): Response
    {
        foreach ($emailTemplate->getAttachments() as $attachment) {
            if ($attachment->getOriginalName() === $filename) {
                $content = $evidenceStorage->read($storage->resolvePath($attachment));
                $response = new Response();
                $response->headers->set('Content-Type', $attachment->getMimetype());
                $response->setContent($content);

                return $response;
            }
        }

        return new Response('No file found', Response::HTTP_NOT_FOUND);
    }

    private function generateContent(
        EmailTemplate $emailTemplate,
        ?Participant $subject,
        ?AchievementDefinition $achievement,
        ?Awarder $awarder,
    ): string
    {
        $assertionId = Uuid::v7();
        $clrId = Uuid::v5(Uuid::fromString('018e5209-5518-757b-8cc1-6fb5f378a7ff'), $assertionId->toRfc4122());

        $credentialIds = [];

        if ($subject instanceof Participant) {
            /** @var ParticipantRepository $participantRepo */
            $participantRepo = $this->entityManager->getRepository(Participant::class);
            $credentialIds = $participantRepo->getAchievementsForParticipant($subject);
        }

        if ($achievement instanceof AchievementDefinition) {
            $credentialIds[] = $achievement->getIdentifier();
        }

        $context = [
            'issuedOn' => new \DateTimeImmutable(),
            'assertionId' => 'urn:uuid:' . $assertionId->toRfc4122(),
            'clrId' => 'urn:uuid:' . $clrId->toRfc4122(),
            'requestIdentity' => Uuid::v7()->toRfc4122(),
            'pathway' => $subject?->getSubscribedPathway()->getName(),
            'pathwayEmailTemplate' => $subject?->getSubscribedPathway()->getEmailTemplate(),
            'pathwayFinalCredential' => $subject?->getSubscribedPathway()->getFinalCredential()->getIdentifier(),
            'credentialIds' => $credentialIds,
        ];
        $templateVars = ['awarder' => $awarder, 'achievement' => $achievement, 'subject' => $subject, 'context' => $context];

        $template = $this->twig->createTemplate($emailTemplate->getTemplate());
        $content = $template->render($templateVars);
        // 2nd pass to replace variables that had variables in their content
        $template = $this->twig->createTemplate($content);
        return $template->render([]);
    }
}
