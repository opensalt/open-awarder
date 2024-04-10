<?php

namespace App\Controller;

use App\Entity\Awarder;
use App\Form\AwarderType;
use App\Repository\AwarderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/awarder')]
#[IsGranted('ROLE_ADMIN')]
class AwarderController extends AbstractController
{
    #[Route('/', name: 'app_awarder_index', methods: ['GET'])]
    public function index(AwarderRepository $awarderRepository): Response
    {
        return $this->render('awarder/index.html.twig', [
            'awarders' => $awarderRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_awarder_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $awarder = new Awarder();
        $form = $this->createForm(AwarderType::class, $awarder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($awarder);
            $entityManager->flush();

            return $this->redirectToRoute('app_awarder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('awarder/new.html.twig', [
            'awarder' => $awarder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_awarder_show', methods: ['GET'])]
    public function show(Awarder $awarder): Response
    {
        return $this->render('awarder/show.html.twig', [
            'awarder' => $awarder,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_awarder_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Awarder $awarder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AwarderType::class, $awarder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_awarder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('awarder/edit.html.twig', [
            'awarder' => $awarder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_awarder_delete', methods: ['POST'])]
    public function delete(Request $request, Awarder $awarder, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$awarder->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($awarder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_awarder_index', [], Response::HTTP_SEE_OTHER);
    }
}
