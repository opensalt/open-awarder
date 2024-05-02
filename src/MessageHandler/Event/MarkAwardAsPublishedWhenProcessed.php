<?php

declare(strict_types=1);

namespace App\MessageHandler\Event;

use App\Enums\AwardState;
use App\Message\Event\AwardWasProcessedEvent;
use App\Message\Event\AwardWasPublishedEvent;
use App\Repository\AwardRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
readonly final class MarkAwardAsPublishedWhenProcessed
{
    public function __construct(
        private AwardRepository $awardRepository,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(AwardWasProcessedEvent $message): void
    {
        $award = $this->awardRepository->find($message->awardId);
        if ($award?->getState() !== AwardState::OcpProcessed) {
            return;
        }

        // Update workflow status
        $this->awardRepository->updateWorkflowStatus($message->awardId, AwardState::Published);

        $this->bus->dispatch(new AwardWasPublishedEvent($message->awardId), [
            new DispatchAfterCurrentBusStamp(),
        ]);
    }
}
