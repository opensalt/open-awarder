<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\AchievementDefinition;
use App\Entity\AwardTemplate;
use App\Entity\EmailTemplate;
use App\Service\TwigVariables;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::prePersist)]
readonly class UpdateFieldsEventListener
{
    public function __construct(
        private TwigVariables $twigVariables,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$this->canActOn($entity)) {
            return;
        }

        $entity->setFields($this->getVariables($entity));
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$this->canActOn($entity)) {
            return;
        }

        $entity->setFields($this->getVariables($entity));
    }

    private function canActOn(object $entity): bool
    {
        return match ($entity::class) {
            AchievementDefinition::class, AwardTemplate::class, EmailTemplate::class => true,
            default => false,
        };
    }

    /**
     * @return array<array-key, mixed>
     */
    private function getVariables(object $entity): array
    {
        return match ($entity::class) {
            AchievementDefinition::class => $this->twigVariables->getVariables($entity->getDefinitionString()),
            AwardTemplate::class => $this->twigVariables->getVariables(json_encode($entity->getTemplate() ?? '', JSON_THROW_ON_ERROR)),
            EmailTemplate::class => $this->twigVariables->getVariables($entity->getTemplate() ?? ''),
            default => [],
        };
    }
}
