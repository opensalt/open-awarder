<?php

declare(strict_types=1);

namespace App\Enums;

enum AwarderState: string
{
    case Active = 'Active';
    case Suspended = 'Suspended';
}
