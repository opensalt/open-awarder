<?php

declare(strict_types=1);

namespace App\Enums;

enum ParticipantState: string
{
    case Active = 'Active';
    case Suspended = 'Suspended';
}
