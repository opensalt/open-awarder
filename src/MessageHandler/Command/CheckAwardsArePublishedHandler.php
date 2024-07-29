<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Enums\AwardState;
use App\Enums\OcpRequestStatus;
use App\Message\Command\CheckAwardsArePublished;
use App\Message\Event\AwardWasProcessedEvent;
use App\Repository\AwardRepository;
use App\Service\OcpPublisher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
final readonly class CheckAwardsArePublishedHandler
{
    public function __construct(
        private OcpPublisher $ocpPublisher,
        private AwardRepository $awardRepository,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(CheckAwardsArePublished $message): void
    {
        $toCheck = $this->awardRepository->findBy(['state' => AwardState::OcpProcessing], ['lastUpdated' => 'ASC'], 10);

        foreach ($toCheck as $award) {
            $status = $this->ocpPublisher->getRequestStatus($award);
            $award->setLastResponse($status);
            $award->setLastUpdated(new \DateTimeImmutable());
            $this->awardRepository->save($award);

            if ((($status['status'] ?? null) === OcpRequestStatus::Complete->value) && in_array(($status['pushed'] ?? null), [null, true], true)) {
                $this->awardRepository->updateWorkflowStatus($award->getId(), AwardState::OcpProcessed);

                $this->bus->dispatch(new AwardWasProcessedEvent($award->getId()), [
                    new DispatchAfterCurrentBusStamp(),
                ]);
            }
        }
    }
}
