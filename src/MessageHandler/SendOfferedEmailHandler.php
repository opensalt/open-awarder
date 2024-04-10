<?php

namespace App\MessageHandler;

use App\Message\SendOfferedEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendOfferedEmailHandler
{
    public function __invoke(SendOfferedEmail $message): void
    {
        // TODO: Send email to recipient about offer
    }
}
