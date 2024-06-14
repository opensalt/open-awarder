<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\AchievementDefinition;
use App\Entity\Awarder;
use App\Entity\AwardTemplate;
use App\Entity\EmailTemplate;
use App\Entity\Participant;
use App\Enums\AwardState;

class MakeAward
{
    public ?Awarder $awarder = null;

    public ?Participant $subject = null;

    public ?AchievementDefinition $achievement = null;

    /** @var array<array-key, mixed>|null */
    public ?array $results = null;

    /** @var array<array-key, mixed>|null */
    public ?array $evidence = null;

    public ?AwardState $state = AwardState::Pending;

    public ?AwardTemplate $awardTemplate = null;

    public ?EmailTemplate $emailTemplate = null;

    /** @var array<array-key, mixed> */
    public array $vars = [];
}
