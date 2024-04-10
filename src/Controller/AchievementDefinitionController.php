<?php

namespace App\Controller;

use App\Entity\AchievementDefinition;
use App\Form\AchievementDefinitionType;
use App\Repository\AchievementDefinitionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/achievement/definition')]
class AchievementDefinitionController extends AbstractController
{
    #[Route('/', name: 'app_achievement_definition_index', methods: ['GET'])]
    public function index(AchievementDefinitionRepository $achievementDefinitionRepository): Response
    {
        return $this->render('achievement_definition/index.html.twig', [
            'achievement_definitions' => $achievementDefinitionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_achievement_definition_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $achievementDefinition = new AchievementDefinition();
        $form = $this->createForm(AchievementDefinitionType::class, $achievementDefinition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($achievementDefinition);
            $entityManager->flush();

            return $this->redirectToRoute('app_achievement_definition_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('achievement_definition/new.html.twig', [
            'achievement_definition' => $achievementDefinition,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_achievement_definition_show', methods: ['GET'])]
    public function show(AchievementDefinition $achievementDefinition): Response
    {
        return $this->render('achievement_definition/show.html.twig', [
            'achievement_definition' => $achievementDefinition,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_achievement_definition_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AchievementDefinition $achievementDefinition, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AchievementDefinitionType::class, $achievementDefinition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_achievement_definition_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('achievement_definition/edit.html.twig', [
            'achievement_definition' => $achievementDefinition,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_achievement_definition_delete', methods: ['POST'])]
    public function delete(Request $request, AchievementDefinition $achievementDefinition, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$achievementDefinition->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($achievementDefinition);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_achievement_definition_index', [], Response::HTTP_SEE_OTHER);
    }
}
