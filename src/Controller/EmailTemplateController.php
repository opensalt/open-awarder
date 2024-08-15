<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AchievementDefinition;
use App\Entity\Awarder;
use App\Entity\EmailAttachment;
use App\Entity\EmailTemplate;
use App\Entity\Participant;
use App\Enums\ParticipantState;
use App\Form\EmailTemplateType;
use App\Repository\EmailTemplateRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use Vich\UploaderBundle\Storage\StorageInterface;

#[Route('/template/email')]
#[IsGranted('ROLE_ADMIN')]
class EmailTemplateController extends AbstractController
{
    #[Route('/', name: 'app_email_template_index', methods: ['GET'])]
    public function index(EmailTemplateRepository $emailTemplateRepository): Response
    {
        return $this->render('email_template/index.html.twig', [
            'email_templates' => $emailTemplateRepository->findBy([], ['id' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_email_template_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $emailTemplate = new EmailTemplate();
        $form = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
    public function send(EmailTemplate $emailTemplate): Response
    {
        return $this->render('email_template/send.html.twig', [
            'email_template' => $emailTemplate,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_email_template_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EmailTemplate $emailTemplate, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
    public function previewSetup(EmailTemplate $emailTemplate, Environment $twig, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder([])
            ->add('awarder', EntityType::class, [
                'class' => Awarder::class,
                'query_builder' => static fn($er) => $er->createQueryBuilder('a')
                    ->addOrderBy('a.name', 'ASC'),
                'choice_label' => 'name',
                'required' => false,
                'translation_domain' => false,
                'placeholder' => 'Select the awarder',
                'attr' => ['onchange' => 'updatePreview()'],
            ])
            ->add('achievement', EntityType::class, [
                'class' => AchievementDefinition::class,
                'query_builder' => static fn($er) => $er->createQueryBuilder('a')
                    ->addOrderBy('a.name', 'ASC'),
                'choice_label' => 'name',
                'required' => false,
                'translation_domain' => false,
                'placeholder' => 'Select the achievement being awarded',
                'attr' => ['onchange' => 'updatePreview()'],
            ])
            ->add('participant', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => static fn(Participant $participant): string => $participant->getFirstName().' '.$participant->getLastName().' - '.$participant->getEmail(),
                'query_builder' => static fn($er) => $er->createQueryBuilder('p')
                    ->where('p.state = :state')
                    ->setParameter('state', ParticipantState::Active)
                    ->andWhere('p.acceptedTerms = true')
                    ->orderBy('p.lastName', 'ASC')
                    ->addOrderBy('p.firstName', 'ASC')
                    ->addOrderBy('p.email', 'ASC'),
                'required' => false,
                'translation_domain' => false,
                'placeholder' => 'Select the person the award is to',
                'attr' => ['onchange' => 'updatePreview()'],
            ])
            ->getForm();

        return $this->render('email_template/preview.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id:emailTemplate}/preview/{awarder}/{achievement}/{subject}', name: 'app_email_template_preview', methods: ['GET'])]
    public function preview(
        EmailTemplate $emailTemplate,
        ?Awarder $awarder,
        ?AchievementDefinition $achievement,
        ?Participant $subject,
        Environment $twig,
        EntityManagerInterface $entityManager
    ): Response
    {
        $response = new Response();

        try {
            $assertionId = Uuid::v7();
            $clrId = Uuid::v5(Uuid::fromString('018e5209-5518-757b-8cc1-6fb5f378a7ff'), $assertionId->toRfc4122());

            $credentialIds = [];

            if ($subject instanceof Participant) {
                /** @var ParticipantRepository $participantRepo */
                $participantRepo = $entityManager->getRepository(Participant::class);
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

            $template = $twig->createTemplate($emailTemplate->getTemplate());
            $content = $template->render($templateVars);
            // 2nd pass to replace variables that had variables in their content
            $template = $twig->createTemplate($content);
            $content = $template->render([]);

            $content = preg_replace('#\bcid:#', 'preview/', $content);

            $response->setContent($content);
        } catch (\Throwable $throwable) {
            $response = new Response($throwable->getMessage(), Response::HTTP_PAYMENT_REQUIRED);
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
}
