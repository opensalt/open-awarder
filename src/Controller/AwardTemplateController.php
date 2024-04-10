<?php

namespace App\Controller;

use App\Entity\AwardTemplate;
use App\Form\AwardTemplateType;
use App\Repository\AwardTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/template/award')]
class AwardTemplateController extends AbstractController
{
    #[Route('/', name: 'app_award_template_index', methods: ['GET'])]
    public function index(AwardTemplateRepository $awardTemplateRepository): Response
    {
        return $this->render('award_template/index.html.twig', [
            'award_templates' => $awardTemplateRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_award_template_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $awardTemplate = new AwardTemplate();
        $form = $this->createForm(AwardTemplateType::class, $awardTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($awardTemplate);
            $entityManager->flush();

            return $this->redirectToRoute('app_award_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('award_template/new.html.twig', [
            'award_template' => $awardTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_award_template_show', methods: ['GET'])]
    public function show(AwardTemplate $awardTemplate): Response
    {
        return $this->render('award_template/show.html.twig', [
            'award_template' => $awardTemplate,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_award_template_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AwardTemplate $awardTemplate, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AwardTemplateType::class, $awardTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_award_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('award_template/edit.html.twig', [
            'award_template' => $awardTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_award_template_delete', methods: ['POST'])]
    public function delete(Request $request, AwardTemplate $awardTemplate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$awardTemplate->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($awardTemplate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_award_template_index', [], Response::HTTP_SEE_OTHER);
    }
}
