<?php

namespace App\Message;

use Symfony\Component\Uid\Uuid;

readonly final class SendPathwayStatusEmail
{
    public function __construct(public Uuid $subjectId)
    {
    }
}
