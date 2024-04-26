<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enums\AwardState;
use App\Enums\OcpRequestStatus;
use App\Message\CheckIfAwardPublished;
use App\Message\OfferAward;
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

            if (($status['status'] ?? null) === OcpRequestStatus::Complete->value) {
                $this->awardRepository->updateWorkflowStatus($award->getId(), AwardState::OcpProcessed);

                // Send email to recipient about offer if OCP is complete
                $this->bus->dispatch(
                    (new Envelope(new OfferAward($award->getId())))
                        ->with(new DispatchAfterCurrentBusStamp())
                );

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
