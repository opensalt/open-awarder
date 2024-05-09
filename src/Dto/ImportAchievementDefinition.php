<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ImportAchievementDefinition
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Url]
        public ?string $uri = null,
    ) {
    }
}
