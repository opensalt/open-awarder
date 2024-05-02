<?php

declare(strict_types=1);

namespace App\Message\Command;

use Symfony\Component\Uid\Uuid;

final readonly class UpdateAwardStatus
{
    public function __construct(
        public Uuid $awardId,
    ) {
    }
}
