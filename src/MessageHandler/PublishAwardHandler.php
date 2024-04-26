<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enums\AwardState;
use App\Message\CheckIfAwardPublished;
use App\Message\PublishAward;
use App\Repository\AwardRepository;
use App\Service\OcpPublisher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
final readonly class PublishAwardHandler
{
    public function __construct(
        private AwardRepository $awardRepository,
        private OcpPublisher $ocpPublisher,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(PublishAward $message): void
    {
        $award = $this->awardRepository->findAwardToPublish($message->awardId);
        if ($award?->getState() !== AwardState::Publishing) {
            // No award or already published
            return;
        }

        // Publish to Open Credential Publisher
        try {
            $this->ocpPublisher->publishAward($award);
        } catch (\Throwable) {
            $this->awardRepository->updateWorkflowStatus($message->awardId, AwardState::Failed);

            return;
        }

        // Update workflow status
        $this->awardRepository->updateWorkflowStatus($message->awardId, AwardState::OcpProcessing);

        // Check the status in a few minutes
        $this->bus->dispatch(
            new Envelope(new CheckIfAwardPublished($message->awardId), [
                DelayStamp::delayFor(new \DateInterval('PT1M')),
                new DispatchAfterCurrentBusStamp()
            ])
        );
    }
}
