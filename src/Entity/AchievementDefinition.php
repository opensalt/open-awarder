<?php

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

    #[ORM\ManyToMany(targetEntity: Awarder::class, mappedBy: 'achievements')]
    private Collection $awarders;

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
}
