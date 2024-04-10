<?php

namespace App\Enums;

enum ParticipantState: string
{
    case Active = 'Active';
    case Suspended = 'Suspended';
}
