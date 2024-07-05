<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\EmailAttachment;
use App\Entity\EmailTemplate;
use App\Form\EmailTemplateType;
use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

            return $this->redirectToRoute('app_email_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('email_template/edit.html.twig', [
            'email_template' => $emailTemplate,
            'form' => $form,
        ]);
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
}
