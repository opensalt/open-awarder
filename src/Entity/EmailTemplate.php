<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EmailTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EmailTemplateRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'This name is already used.')]
class EmailTemplate
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(name: 'from_address', nullable: true)]
    private ?string $from = null;

    #[ORM\Column(nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $template = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $fields = null;

    #[ORM\ManyToMany(targetEntity: Awarder::class, inversedBy: 'emailTemplates')]
    #[ORM\JoinTable(name: 'email_template_awarders')]
    private Collection $awarders;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->awarders = new ArrayCollection();
    }

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

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): static
    {
        $this->template = $template;

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
            $awarder->addEmailTemplate($this);
        }

        return $this;
    }

    public function removeAwarder(Awarder $awarder): static
    {
        if ($this->awarders->removeElement($awarder)) {
            $awarder->removeEmailTemplate($this);
        }

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

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(?string $from): EmailTemplate
    {
        $this->from = $from;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): EmailTemplate
    {
        $this->subject = $subject;

        return $this;
    }
}
