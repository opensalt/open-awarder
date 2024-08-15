<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Entity\Email;
use App\Entity\EmailTemplate;
use App\Enums\AwardState;
use App\Enums\EmailState;
use App\Message\Command\SendEmail;
use App\Message\Command\SendOfferedEmail;
use App\Repository\AwardRepository;
use App\Repository\EmailRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
final readonly class SendOfferedEmailHandler
{
    public function __construct(
        private AwardRepository $awardRepository,
        private EmailRepository $emailRepository,
        private MessageBusInterface $bus,
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

        $pendingEmail = new Email(EmailState::Ready);
        $pendingEmail->setFromEmailTemplate($emailTemplate);
        $pendingEmail->setRenderedEmail($emailContent);
        $pendingEmail->setAward($award);
        $pendingEmail->setSubject($award->getSubject());
        $this->emailRepository->save($pendingEmail);

        $this->bus->dispatch(
            new Envelope(new SendEmail($pendingEmail->getId()), [
                new DispatchAfterCurrentBusStamp()
            ])
        );
    }
}
