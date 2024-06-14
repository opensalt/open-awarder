<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AchievementDefinitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AchievementDefinitionRepository::class)]
#[ORM\UniqueConstraint(columns: ['uri'])]
#[UniqueEntity(fields: ['uri'], message: 'This URI is already defined.')]
class AchievementDefinition
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $uri = null;

    #[ORM\Column(nullable: true)]
    private ?string $identifier = null;

    /**
     * @var array<array-key, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $definition = null;

    /**
     * @var Collection<array-key, Awarder>
     */
    #[ORM\ManyToMany(targetEntity: Awarder::class, inversedBy: 'achievements')]
    #[ORM\JoinTable(name: 'achievements_awarders')]
    private Collection $awarders;

    /**
     * @var array<array-key, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $fields = null;

    public function __construct()
    {
        $this->awarders = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(string $uri): static
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return Collection<int, Awarder>
     */
    public function getAwarders(): Collection
    {
        return $this->awarders;
    }

    public function addAwarder(Awarder $awarder): static
    {
        if (!$this->awarders->contains($awarder)) {
            $this->awarders->add($awarder);
            $awarder->addAchievement($this);
        }

        return $this;
    }

    public function removeAwarder(Awarder $awarder): static
    {
        if ($this->awarders->removeElement($awarder)) {
            $awarder->removeAchievement($this);
        }

        return $this;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    public function getDefinition(): ?array
    {
        return $this->definition;
    }

    /**
     * @param array<array-key, mixed>|null $definition
     */
    public function setDefinition(?array $definition): AchievementDefinition
    {
        $this->definition = $definition;

        return $this;
    }

    public function getDefinitionString(): string
    {
        return json_encode($this->definition, JSON_THROW_ON_ERROR|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): AchievementDefinition
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @param array<array-key, mixed>|null $fields
     */
    public function setFields(?array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }
}
