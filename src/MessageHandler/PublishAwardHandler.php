<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enums\AwardState;
use App\Message\PublishAward;
use App\Repository\AwardRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class PublishAwardHandler
{
    public function __construct(public AwardRepository $awardRepository)
    {
    }

    public function __invoke(PublishAward $message): void
    {
        // TODO: Publish to Open Credential Publisher
        // Update workflow status
        $this->awardRepository->updateWorkflowStatus($message->awardId, AwardState::Publishing);
    }
}
