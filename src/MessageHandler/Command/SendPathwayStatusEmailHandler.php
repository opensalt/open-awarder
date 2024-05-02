<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Message\Command\SendPathwayStatusEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendPathwayStatusEmailHandler
{
    public function __invoke(SendPathwayStatusEmail $message): void
    {
        // TODO: Send email with pathway status to subject
    }
}
