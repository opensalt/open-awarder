<?php

declare(strict_types=1);

namespace App\Controller;

use App\DataTable\Type\ParticipantTableType;
use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Repository\PathwayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/participant')]
#[IsGranted('ROLE_ADMIN')]
class ParticipantController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PathwayRepository $pathwayRepository,
    ) {
    }

    #[Route('/', name: 'app_participant_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $dataTable = $this->createDataTable(ParticipantTableType::class);
        $dataTable->handleRequest($request);

        return $this->render('participant/index.html.twig', [
            'table' => $dataTable->createView(),
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
    public function edit(Request $request, Participant $participant): Response
    {
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_delete', methods: ['POST'])]
    public function delete(Request $request, Participant $participant): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->getPayload()->get('_token'))) {
            $this->entityManager->remove($participant);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/import', name: 'app_participant_import', methods: ['GET', 'POST'], priority: 10)]
    public function import(Request $request): Response
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
                                $rec['pathway'] = $this->pathwayRepository->getPathwayFromName($rec['pathway']);

                                $participant = Participant::fromCsv($rec);
                            } catch (\Throwable $e) {
                                if (str_contains($e->getMessage(), 'Undefined array key')) {
                                    throw new \ErrorException(
                                        message: (preg_replace('/.*"([^"]+)"/', '$1', $e->getMessage()).' column missing.'),
                                        code: $e->getCode(),
                                        previous: $e
                                    );
                                }

                                throw new \ErrorException(message: $e->getMessage(), code: $e->getCode(), previous: $e);
                            }

                            $this->entityManager->persist($participant);
                        }

                        $this->entityManager->flush();
                    }

                    return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
                }
            } catch (\Throwable $e) {
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

    #[Route('/export', name: 'app_participant_export', methods: ['GET'], priority: 10)]
    public function export(ParticipantRepository $repo): Response
    {
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'participants.csv')
        );
        $response->setCallback(function () use ($repo): void {
            $fd = fopen('php://output', 'w+');

            fputcsv($fd, [
                'firstName',
                'lastName',
                'email',
                'pathway',
                'acceptedTerms',
                'phone',
                'aboutMe',
            ]);

            $participants = $repo->getParticipants();

            /** @var Participant $participant */
            foreach ($participants as $participant) {
                fputcsv($fd, [
                    $participant->getFirstName(),
                    $participant->getLastName(),
                    $participant->getEmail(),
                    $participant->getSubscribedPathway()?->getName(),
                    $participant->isAcceptedTerms() ? 'Yes' : 'No',
                    $participant->getPhone(),
                    $participant->getAboutMe(),
                ]);
            }

            fclose($fd);
        });

        return $response;
    }
}
