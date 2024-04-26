<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AwardTemplateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AwardTemplateRepository::class)]
#[ORM\UniqueConstraint(columns: ['name'])]
#[UniqueEntity(fields: ['name'], message: 'This name is already used.')]
class AwardTemplate
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $template = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $fields = null;

    #[ORM\ManyToOne(inversedBy: 'awardTemplates')]
    private ?Awarder $awarder = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
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

    public function getTemplate(): ?array
    {
        return $this->template;
    }

    public function setTemplate(array $template): static
    {
        $this->template = $template;

        return $this;
    }

    public function getAwarder(): ?Awarder
    {
        return $this->awarder;
    }

    public function setAwarder(?Awarder $awarder): static
    {
        $this->awarder = $awarder;

        return $this;
    }

    public function getFields(): ?array
    {
        return $this->fields;
    }

    public function setFields(?array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }
}
