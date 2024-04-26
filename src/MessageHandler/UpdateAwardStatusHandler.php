<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateAwardStatus;
use App\Repository\AwardRepository;
use App\Service\OcpPublisher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateAwardStatusHandler
{
    public function __construct(
        private AwardRepository $awardRepository,
        private OcpPublisher    $ocpPublisher,
    ) {
    }

    public function __invoke(UpdateAwardStatus $message): void
    {
        $award = $this->awardRepository->find($message->awardId);
        if (null === $award) {
            return;
        }
        if (null === $award->getRequestId()) {
            return;
        }

        $response = $this->ocpPublisher->getRequestStatus($award);
        $award->setLastResponse($response);
        $this->awardRepository->save($award);
    }
}
