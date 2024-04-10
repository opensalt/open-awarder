<?php

namespace App\Controller;

use App\Entity\Award;
use App\Enums\AwardState;
use App\Form\AwardType;
use App\Message\PublishAward;
use App\Message\RevokeAward;
use App\Repository\AwardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/award')]
class AwardController extends AbstractController
{
    #[Route('/', name: 'app_award_index', methods: ['GET'])]
    public function index(AwardRepository $awardRepository): Response
    {
        return $this->render('award/index.html.twig', [
            'awards' => $awardRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_award_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $award = new Award();
        $form = $this->createForm(AwardType::class, $award);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($award);
            $entityManager->flush();

            return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
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
            $bus->dispatch(new PublishAward($award->getId()));
            $award->setState(AwardState::Publishing);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_award_index', [], Response::HTTP_SEE_OTHER);
    }
}
