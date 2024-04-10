<?php

namespace App\MessageHandler;

use App\Enums\AwardState;
use App\Message\RevokeAward;
use App\Repository\AwardRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RevokeAwardHandler
{
    public function __construct(public AwardRepository $awardRepository)
    {
    }

    public function __invoke(RevokeAward $message): void
    {
        $award = $this->awardRepository->find($message->awardId);
        if (null === $award) {
            return;
        }

        if (in_array($award->getState(), [AwardState::Pending, AwardState::Publishing], true)) {
            $this->awardRepository->updateWorkflowStatus($message->awardId, AwardState::Revoked);

            return;
        }

        // TODO: Revoke award in OCP

        // Update award status
        $this->awardRepository->updateWorkflowStatus($message->awardId, AwardState::Revoked);
    }
}
