<?php

namespace App\Enums;

enum AwardState: string
{
    case Pending = 'Pending';
    case Publishing = 'Publishing';
    case Offered = 'Offered';
    case Accepted = 'Accepted';
    case Revoking = 'Revoking';
    case Revoked = 'Revoked';
}
