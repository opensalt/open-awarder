<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Entity\EmailTemplate;
use App\Enums\EmailState;
use App\Message\Command\SendEmail;
use App\Repository\EmailRepository;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Vich\UploaderBundle\Storage\StorageInterface;

#[AsMessageHandler]
final readonly class SendEmailHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private EmailRepository $emailRepository,
        private StorageInterface $storage,
        private FilesystemOperator $evidenceStorage,
    ) {
    }

    public function __invoke(SendEmail $message): void
    {
        $pendingEmail = $this->emailRepository->find($message->emailId);
        if ($pendingEmail?->getState() !== EmailState::Ready) {
            return;
        }

        // Update workflow status
        $this->emailRepository->updateWorkflowStatus($message->emailId, EmailState::Sending);

        $emailTemplate = $pendingEmail->getEmailTemplate();
        if (!($emailTemplate instanceof EmailTemplate)) {
            // No email to be sent
            return;
        }

        $emailContent = $pendingEmail->getRenderedEmail();

        $email = (new Email())
            ->from($pendingEmail->getEmailFrom())
            ->to($pendingEmail->getSubject()->getEmail())
            ->subject($pendingEmail->getEmailSubject())
            ->html($emailContent)
        ;

        foreach ($emailTemplate->getAttachments() as $attachment) {
            $content = $this->evidenceStorage->read($this->storage->resolvePath($attachment));
            $email->addPart((new DataPart($content, $attachment->getOriginalName(), $attachment->getMimetype()))->asInline());
        }

        $this->mailer->send($email);

        // Update workflow status
        $this->emailRepository->updateWorkflowStatus($message->emailId, EmailState::Sent);
    }
}
