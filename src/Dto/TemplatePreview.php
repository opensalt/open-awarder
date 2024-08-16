<?php

namespace App\Dto;

use App\Entity\AchievementDefinition;
use App\Entity\Awarder;
use App\Entity\EmailTemplate;
use App\Entity\Participant;

class TemplatePreview
{
    public ?EmailTemplate $emailTemplate;
    public ?Participant $participant;
    public ?AchievementDefinition $achievement;
    public ?Awarder $awarder;
}
