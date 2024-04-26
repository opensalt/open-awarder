<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enums\AwardState;
use App\Message\RevokeAward;
use App\Message\UpdateAwardStatus;
use App\Repository\AwardRepository;
use App\Service\OcpPublisher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
final readonly class RevokeAwardHandler
{
    public function __construct(
        private AwardRepository $awardRepository,
        private OcpPublisher $ocpPublisher,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(RevokeAward $message): void
    {
        $award = $this->awardRepository->find($message->awardId);
        if (null === $award) {
            return;
        }

        if ($award->getState() === AwardState::Revoked) {
            // Already revoked. Do nothing.
            return;
        };

        if (null === $award->getRequestId()) {
            // No request id, so just mark it as revoked
            $this->awardRepository->updateWorkflowStatus($message->awardId, AwardState::Revoked);

            return;
        }

        if (in_array($award->getState(), [AwardState::Pending, AwardState::Publishing], true)) {
            // Not yet published, so just mark it as revoked
            $this->awardRepository->updateWorkflowStatus($message->awardId, AwardState::Revoked);

            return;
        }

        // Revoke award in OCP
        $this->ocpPublisher->revokeAward($award);

        // Update award status
        $this->awardRepository->updateWorkflowStatus($message->awardId, AwardState::Revoked);
        $this->bus->dispatch(new UpdateAwardStatus($message->awardId), [
            new DelayStamp(10000),
            new DispatchAfterCurrentBusStamp(),
        ]);
    }
}
