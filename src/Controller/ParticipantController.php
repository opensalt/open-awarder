<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/participant')]
class ParticipantController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    #[Route('/', name: 'app_participant_index', methods: ['GET'])]
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findBy([], ['id' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_participant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participant);
            $entityManager->flush();

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_show', methods: ['GET'])]
    public function show(Participant $participant): Response
    {
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_delete', methods: ['POST'])]
    public function delete(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($participant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/import', name: 'app_participant_import', methods: ['GET', 'POST'], priority: 10)]
    public function import(Request $request, EntityManagerInterface $entityManager): Response
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
                        $participants = $reader->getRecords();

                        foreach ($participants as $rec) {
                            try {
                                $participant = Participant::fromCsv($rec);
                            } catch (\ErrorException $e) {
                                if (str_contains($e->getMessage(), 'Undefined array key')) {
                                    throw new \ErrorException((preg_replace('/.*"([^"]+)"/', '$1', $e->getMessage()).' column missing.'), $e->getCode(), $e);
                                }

                                throw new \ErrorException($e->getMessage(), $e->getCode(), $e);
                            }

                            $entityManager->persist($participant);
                        }

                        $entityManager->flush();
                    }

                    return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
                }
            } catch (\Throwable $e) {
                dump($e);
                $uploadForm->get('file')->addError(new FormError(message: 'Upload failed: '.$e->getMessage()));

                return $this->render('participant/import.html.twig', [
                    'uploadForm' => $uploadForm->createView(),
                ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
            }
        }

        return $this->render('participant/import.html.twig', [
            'uploadForm' => $uploadForm->createView(),
        ]);
    }
}
