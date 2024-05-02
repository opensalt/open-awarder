<?php

declare(strict_types=1);

namespace App\Message\Command;

use Symfony\Component\Uid\Uuid;

readonly final class SendPathwayStatusEmail
{
    public function __construct(public Uuid $subjectId)
    {
    }
}
