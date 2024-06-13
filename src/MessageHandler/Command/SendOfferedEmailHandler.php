<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Entity\EmailTemplate;
use App\Enums\AwardState;
use App\Message\Command\SendOfferedEmail;
use App\Repository\AwardRepository;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Vich\UploaderBundle\Storage\StorageInterface;

#[AsMessageHandler]
final readonly class SendOfferedEmailHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private AwardRepository $awardRepository,
        private StorageInterface $storage,
        private FilesystemOperator $evidenceStorage,
    ) {
    }

    public function __invoke(SendOfferedEmail $message): void
    {
        $award = $this->awardRepository->find($message->awardId);
        if ($award?->getState() !== AwardState::Published) {
            return;
        }

        // Update workflow status
        $this->awardRepository->updateWorkflowStatus($message->awardId, AwardState::Offered);

        $emailTemplate = $award->getEmailTemplate();
        if (!($emailTemplate instanceof EmailTemplate)) {
            // No email to be sent
            return;
        }

        $OcpUrl = $award->getLastResponse()['url'] ?? '';
        $emailContent = $award->getAwardEmail();
        $emailContent = str_replace('OCP_ACCEPT_URL', $OcpUrl, (string) $emailContent);

        $email = (new Email())
            ->from($award->getAwardEmailFrom())
            ->to($award->getSubject()->getEmail())
            ->subject($award->getAwardEmailSubject())
            ->html($emailContent)
        ;

        foreach ($emailTemplate->getAttachments() as $attachment) {
            $content = $this->evidenceStorage->read($this->storage->resolvePath($attachment));
            $email->addPart((new DataPart($content, $attachment->getOriginalName(), $attachment->getMimetype()))->asInline());
        }

        $this->mailer->send($email);
    }
}
