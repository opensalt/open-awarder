<?php

declare(strict_types=1);

namespace App\Enums;

enum OcpRequestStatus: string
{
    case Accepted = 'Accepted';
    case Signing = 'Signing';
    case Pushing = 'Pushing';
    case Complete = 'Complete';
    case Revoked = 'Revoked';
}
