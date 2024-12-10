<?php

declare(strict_types=1);

namespace App\Controller;

use App\DataTable\Type\EmailDataTableType;
use App\Entity\Email;
use Doctrine\ORM\EntityManagerInterface;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Storage\StorageInterface;

#[Route('/email')]
#[IsGranted('ROLE_ADMIN')]
class EmailController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    #[Route('/', name: 'app_email_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $dataTable = $this->createDataTable(EmailDataTableType::class);
        $dataTable->handleRequest($request);

        return $this->render('email/index.html.twig', [
            'table' => $dataTable->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_email_show', methods: ['GET'])]
    public function show(Email $email): Response
    {
        return $this->render('email/show.html.twig', [
            'email' => $email,
        ]);
    }

    #[Route('/{id}', name: 'app_email_delete', methods: ['POST'])]
    public function delete(Request $request, Email $email, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$email->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($email);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_email_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/preview', name: 'app_email_preview', methods: ['GET'])]
    public function preview(Email $email, ?Profiler $profiler): Response
    {
        if ($profiler instanceof Profiler) {
            // Disable displaying the profiler in the iframe
            $profiler->disable();
        }

        $response = new Response();

        $content = $email->getRenderedEmail();
        $content = preg_replace('#\bcid:#', 'preview/', (string) $content);

        $response->setContent($content);

        return $response;
    }

    #[Route('/{id:email}/preview/{filename}', name: 'app_email_preview_file', methods: ['GET'])]
    public function previewFile(Email $email, string $filename, StorageInterface $storage, FilesystemOperator $evidenceStorage): Response
    {
        $emailTemplate = $email->getEmailTemplate();
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
