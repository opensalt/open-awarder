<?php

declare(strict_types=1);

namespace App\Enums;

enum AwardState: string
{
    case Pending = 'Pending';
    case Publishing = 'Publishing';
    case OcpProcessing = 'Processing';
    case OcpProcessed = 'Processed';
    case Published = 'Published';
    case Offered = 'Offered';
    case Accepted = 'Accepted';
    case Revoking = 'Revoking';
    case Revoked = 'Revoked';
    case Failed = 'Failed';
}
