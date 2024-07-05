<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Pathway;
use App\Form\PathwayType;
use App\Repository\PathwayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pathway')]
#[IsGranted('ROLE_ADMIN')]
class PathwayController extends AbstractController
{
    #[Route('/', name: 'app_pathway_index', methods: ['GET'])]
    public function index(PathwayRepository $pathwayRepository): Response
    {
        return $this->render('pathway/index.html.twig', [
            'pathways' => $pathwayRepository->findBy([], ['id' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_pathway_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pathway = new Pathway();
        $form = $this->createForm(PathwayType::class, $pathway);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pathway);
            $entityManager->flush();

            return $this->redirectToRoute('app_pathway_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pathway/new.html.twig', [
            'pathway' => $pathway,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pathway_show', methods: ['GET'])]
    public function show(Pathway $pathway): Response
    {
        return $this->render('pathway/show.html.twig', [
            'pathway' => $pathway,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pathway_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pathway $pathway, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PathwayType::class, $pathway);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_pathway_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pathway/edit.html.twig', [
            'pathway' => $pathway,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pathway_delete', methods: ['POST'])]
    public function delete(Request $request, Pathway $pathway, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pathway->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($pathway);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pathway_index', [], Response::HTTP_SEE_OTHER);
    }
}
