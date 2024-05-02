<?php

declare(strict_types=1);

namespace App\Message\Event;

use Symfony\Component\Uid\Uuid;

readonly final class AwardWasProcessedEvent
{
    public function __construct(public Uuid $awardId)
    {
    }
}
