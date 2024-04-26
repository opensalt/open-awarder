<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SendPathwayStatusEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendPathwayStatusEmailHandler
{
    public function __invoke(SendPathwayStatusEmail $message): void
    {
        // TODO: Send email with pathway status to subject
    }
}
