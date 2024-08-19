<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\AchievementDefinition;
use App\Entity\Awarder;
use App\Entity\EmailTemplate;
use App\Entity\Participant;

class TemplatePreview
{
    public ?EmailTemplate $emailTemplate = null;

    public ?Participant $participant = null;

    public ?AchievementDefinition $achievement = null;

    public ?Awarder $awarder = null;
}
