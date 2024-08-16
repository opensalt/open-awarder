<?php

declare(strict_types=1);

namespace App\Enums;

enum EmailState: string
{
    case Pending = 'Pending';
    case Ready = 'Ready';
    case Sending = 'Sending';
    case Sent = 'Sent';
    case Failed = 'Failed';
}
