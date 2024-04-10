<?php

namespace App\Entity;

use App\Enums\AwardState;
use App\Repository\AwardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: AwardRepository::class)]
#[Broadcast]
class Award
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'awards')]
    private ?Awarder $awarder = null;

    #[ORM\ManyToOne(inversedBy: 'awards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $subject = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?AchievementDefinition $achievement = null;

    #[ORM\Column(nullable: true)]
    private ?array $results = null;

    #[ORM\Column(nullable: true)]
    private ?array $evidence = null;

    #[ORM\Column(length: 255)]
    private ?AwardState $state = AwardState::Pending;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?AwardTemplate $awardTemplate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?EmailTemplate $emailTemplate = null;

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function getSubject(): ?Participant
    {
        return $this->subject;
    }

    public function setSubject(?Participant $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getResults(): ?array
    {
        return $this->results;
    }

    public function setResults(?array $results): static
    {
        $this->results = $results;

        return $this;
    }

    public function getEvidence(): ?array
    {
        return $this->evidence;
    }

    public function setEvidence(?array $evidence): static
    {
        $this->evidence = $evidence;

        return $this;
    }

    public function getState(): ?AwardState
    {
        return $this->state;
    }

    public function setState(AwardState $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getAwardTemplate(): ?AwardTemplate
    {
        return $this->awardTemplate;
    }

    public function setAwardTemplate(?AwardTemplate $awardTemplate): static
    {
        $this->awardTemplate = $awardTemplate;

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

    public function getAchievement(): ?AchievementDefinition
    {
        return $this->achievement;
    }

    public function setAchievement(?AchievementDefinition $achievement): Award
    {
        $this->achievement = $achievement;

        return $this;
    }
}
