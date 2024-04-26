<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Uid\Uuid;

readonly final class CheckIfAwardPublished
{
    public function __construct(public Uuid $awardId, public int $tries = 0)
    {
    }
}
