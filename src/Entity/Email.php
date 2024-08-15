<?php

namespace App\Entity;

use App\Enums\EmailState;
use App\Repository\EmailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: EmailRepository::class)]
#[Broadcast]
class Email
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $subject = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?EmailTemplate $emailTemplate = null;

    #[ORM\Column(length: 255)]
    private ?string $emailFrom = null;

    #[ORM\Column(length: 255)]
    private ?string $emailSubject = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $renderedEmail = null;

    #[ORM\Column(length: 255)]
    private string $status;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $lastUpdated;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Award $award = null;

    public function __construct(EmailState $state = EmailState::Pending)
    {
        $this->id = Uuid::v7();
        $this->lastUpdated = new \DateTimeImmutable();
        $this->status = $state->value;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getSubject(): ?Participant
    {
        return $this->subject;
    }

    public function setSubject(Participant $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getEmailTemplate(): ?EmailTemplate
    {
        return $this->emailTemplate;
    }

    public function setEmailTemplate(?EmailTemplate $emailTemplate): static
    {
        $this->emailTemplate = $emailTemplate;

        return $this;
    }

    public function getRenderedEmail(): ?string
    {
        return $this->renderedEmail;
    }

    public function setRenderedEmail(?string $renderedEmail): static
    {
        $this->renderedEmail = $renderedEmail;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(EmailState|string $status): static
    {
        if ($status instanceof EmailState) {
            $this->status = $status->value;
        } else {
            $this->status = EmailState::from($status)->value;
        }

        return $this;
    }

    public function getState(): EmailState
    {
        return EmailState::from($this->status);
    }

    public function setState(EmailState $state): static
    {
        $this->status = $state->value;

        return $this;
    }

    public function getLastUpdated(): ?\DateTimeImmutable
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTimeImmutable $lastUpdated): static
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function getEmailFrom(): ?string
    {
        return $this->emailFrom;
    }

    public function setEmailFrom(string $emailFrom): static
    {
        $this->emailFrom = $emailFrom;

        return $this;
    }

    public function getEmailSubject(): ?string
    {
        return $this->emailSubject;
    }

    public function setEmailSubject(string $emailSubject): static
    {
        $this->emailSubject = $emailSubject;

        return $this;
    }

    public function getAward(): ?Award
    {
        return $this->award;
    }

    public function setAward(?Award $award): static
    {
        $this->award = $award;

        return $this;
    }

    public function setFromEmailTemplate(EmailTemplate $emailTemplate): static
    {
        $this->emailTemplate = $emailTemplate;
        $this->emailFrom = $emailTemplate->getFrom();
        $this->emailSubject = $emailTemplate->getSubject();

        return $this;
    }
}
