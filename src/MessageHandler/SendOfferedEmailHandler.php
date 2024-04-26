<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enums\AwardState;
use App\Message\SendOfferedEmail;
use App\Repository\AwardRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final readonly class SendOfferedEmailHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private AwardRepository $awardRepository,
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

        $OcpUrl = $award->getLastResponse()['url'] ?? '';
        $emailContent = $award->getAwardEmail();
        $emailContent = str_replace('OCP_ACCEPT_URL', $OcpUrl, (string) $emailContent);

        $email = (new Email())
            ->from($award->getAwardEmailFrom())
            ->to($award->getSubject()->getEmail())
            ->subject($award->getAwardEmailSubject())
            ->html($emailContent)
        ;

        $this->mailer->send($email);
    }
}
