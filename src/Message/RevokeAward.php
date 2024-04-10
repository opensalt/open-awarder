<?php

namespace App\Message;

use Symfony\Component\Uid\Uuid;

readonly final class RevokeAward
{
    public function __construct(public Uuid $awardId)
    {
    }
}
