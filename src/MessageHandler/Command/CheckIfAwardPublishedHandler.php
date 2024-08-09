<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Enums\AwardState;
use App\Enums\OcpRequestStatus;
use App\Message\Command\CheckIfAwardPublished;
use App\Message\Event\AwardWasProcessedEvent;
use App\Repository\AwardRepository;
use App\Service\OcpPublisher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
readonly final class CheckIfAwardPublishedHandler
{
    public function __construct(
        private AwardRepository $awardRepository,
        private OcpPublisher $ocpPublisher,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(CheckIfAwardPublished $message): void
    {
        $award = $this->awardRepository->find($message->awardId);
        if ($award?->getState() !== AwardState::OcpProcessing) {
            return;
        }

        if (null === $award->getRequestId()) {
            return;
        }

        try {
            $status = $this->ocpPublisher->getRequestStatus($award);
            $award->setLastResponse($status);
            $award->setLastUpdated(new \DateTimeImmutable());
            $award->setAcceptUrl($status['url'] ?? null);
            $this->awardRepository->save($award);

            if (($status['hasError'] ?? null) === true) {
                $this->awardRepository->updateWorkflowStatus($award->getId(), AwardState::Failed);

                return;
            }

            if (($status['status'] ?? null) === OcpRequestStatus::PushFailed->value) {
                $this->awardRepository->updateWorkflowStatus($award->getId(), AwardState::Failed);

                return;
            }

            if ((($status['status'] ?? null) === OcpRequestStatus::Complete->value) && in_array(($status['pushed'] ?? null), [null, true], true)) {
                $this->awardRepository->updateWorkflowStatus($award->getId(), AwardState::OcpProcessed);

                $this->bus->dispatch(new AwardWasProcessedEvent($award->getId()), [
                    new DispatchAfterCurrentBusStamp(),
                ]);

                return;
            }
        } catch (\Throwable) {
            // Do nothing, try again later
        }

        if ($message->tries >= 20) {
            // Failed many times, give up
            $this->awardRepository->updateWorkflowStatus($award->getId(), AwardState::Failed);

            return;
        }

        // Otherwise try again later (exponential delay)
        $this->bus->dispatch(
            new Envelope(new CheckIfAwardPublished($message->awardId, $message->tries + 1), [
                new DelayStamp(((int) ((1.3**$message->tries) * 60)) * 1000),
                new DispatchAfterCurrentBusStamp()
            ])
        );
    }
}
