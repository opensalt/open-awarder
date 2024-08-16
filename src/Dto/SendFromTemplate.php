<?php

namespace App\Dto;

use App\Entity\AchievementDefinition;
use App\Entity\Awarder;
use App\Entity\EmailTemplate;
use App\Entity\Participant;
use Symfony\Component\Validator\Constraints\NotBlank;

class SendFromTemplate
{
    public ?EmailTemplate $emailTemplate = null;
    #[NotBlank]
    public ?Participant $participant = null;
    public ?AchievementDefinition $achievement = null;
    public ?Awarder $awarder = null;
}
