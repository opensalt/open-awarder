<?php

namespace App\Entity;

use App\Enums\AwarderState;
use App\Repository\AwarderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AwarderRepository::class)]
#[ORM\UniqueConstraint(columns: ['name'])]
#[UniqueEntity(fields: ['name'], message: 'This name is already used.')]
class Awarder
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $issuerId = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contact = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $protocol = null;

    #[ORM\Column(nullable: true)]
    private ?array $ocpInfo = null;

    #[ORM\Column]
    private AwarderState $state = AwarderState::Active;

    #[ORM\ManyToMany(targetEntity: AchievementDefinition::class, inversedBy: 'awarders')]
    private Collection $achievements;

    #[ORM\OneToMany(targetEntity: AwardTemplate::class, mappedBy: 'awarder')]
    private Collection $awardTemplates;

    #[ORM\OneToMany(targetEntity: EmailTemplate::class, mappedBy: 'awarder')]
    private Collection $emailTemplates;

    #[ORM\OneToMany(targetEntity: Award::class, mappedBy: 'awarder')]
    private Collection $awards;

    public function __construct()
    {
        $this->achievements = new ArrayCollection();
        $this->awardTemplates = new ArrayCollection();
        $this->emailTemplates = new ArrayCollection();
        $this->awards = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getIssuerId(): ?string
    {
        return $this->issuerId;
    }

    public function setIssuerId(string $issuerId): static
    {
        $this->issuerId = $issuerId;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): static
    {
        $this->contact = $contact;

        return $this;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(string $protocol): static
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getOcpInfo(): ?array
    {
        return $this->ocpInfo;
    }

    public function setOcpInfo(?array $ocpInfo): static
    {
        $this->ocpInfo = $ocpInfo;

        return $this;
    }

    /**
     * @return Collection<int, AchievementDefinition>
     */
    public function getAchievements(): Collection
    {
        return $this->achievements;
    }

    public function addAchievement(AchievementDefinition $achievement): static
    {
        if (!$this->achievements->contains($achievement)) {
            $this->achievements->add($achievement);
        }

        return $this;
    }

    public function removeAchievement(AchievementDefinition $achievement): static
    {
        $this->achievements->removeElement($achievement);

        return $this;
    }

    /**
     * @return Collection<int, AwardTemplate>
     */
    public function getAwardTemplates(): Collection
    {
        return $this->awardTemplates;
    }

    public function addAwardTemplate(AwardTemplate $awardTemplate): static
    {
        if (!$this->awardTemplates->contains($awardTemplate)) {
            $this->awardTemplates->add($awardTemplate);
            $awardTemplate->setAwarder($this);
        }

        return $this;
    }

    public function removeAwardTemplate(AwardTemplate $awardTemplate): static
    {
        // set the owning side to null (unless already changed)
        if ($this->awardTemplates->removeElement($awardTemplate) && $awardTemplate->getAwarder() === $this) {
            $awardTemplate->setAwarder(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, EmailTemplate>
     */
    public function getEmailTemplates(): Collection
    {
        return $this->emailTemplates;
    }

    public function addEmailTemplate(EmailTemplate $emailTemplate): static
    {
        if (!$this->emailTemplates->contains($emailTemplate)) {
            $this->emailTemplates->add($emailTemplate);
            $emailTemplate->setAwarder($this);
        }

        return $this;
    }

    public function removeEmailTemplate(EmailTemplate $emailTemplate): static
    {
        // set the owning side to null (unless already changed)
        if ($this->emailTemplates->removeElement($emailTemplate) && $emailTemplate->getAwarder() === $this) {
            $emailTemplate->setAwarder(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Award>
     */
    public function getAwards(): Collection
    {
        return $this->awards;
    }

    public function addAward(Award $award): static
    {
        if (!$this->awards->contains($award)) {
            $this->awards->add($award);
            $award->setAwarder($this);
        }

        return $this;
    }

    public function removeAward(Award $award): static
    {
        // set the owning side to null (unless already changed)
        if ($this->awards->removeElement($award) && $award->getAwarder() === $this) {
            $award->setAwarder(null);
        }

        return $this;
    }

    public function getState(): AwarderState
    {
        return $this->state;
    }

    public function setState(AwarderState $state): Awarder
    {
        $this->state = $state;

        return $this;
    }
}
