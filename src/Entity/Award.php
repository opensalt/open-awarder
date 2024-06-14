<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enums\AwardState;
use App\Repository\AwardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV1;
use Symfony\Component\Uid\UuidV7;
use Symfony\UX\Turbo\Attribute\Broadcast;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: AwardRepository::class)]
#[Vich\Uploadable]
#[Broadcast]
class Award
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'awards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Awarder $awarder = null;

    #[ORM\ManyToOne(inversedBy: 'awards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $subject = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?AchievementDefinition $achievement = null;

    /**
     * @var array<array-key, mixed>|null
     */
    #[ORM\Column(nullable: true)]
    private ?array $results = null;

    #[ORM\Column(length: 255)]
    private ?AwardState $state = AwardState::Pending;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?AwardTemplate $awardTemplate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?EmailTemplate $emailTemplate = null;

    /**
     * @var array<array-key, mixed>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $awardJson = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $awardEmail = null;

    #[ORM\Column(nullable: true)]
    private ?string $awardEmailFrom = null;

    #[ORM\Column(nullable: true)]
    private ?string $awardEmailSubject = null;

    #[ORM\Column(nullable: true)]
    private ?string $requestId = null;

    /**
     * @var array<array-key, mixed>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $lastResponse = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $acceptUrl = null;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private \DateTimeInterface $lastUpdated;

    /**
     * @var Collection<int, EvidenceFile>
     */
    #[ORM\OneToMany(targetEntity: EvidenceFile::class, mappedBy: 'award', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $evidence;

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id instanceof Uuid ? $id : Uuid::v7();

        $this->lastUpdated = new \DateTimeImmutable();
        $this->evidence = new ArrayCollection();
    }

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

    /**
     * @return array<array-key, mixed>|null
     */
    public function getResults(): ?array
    {
        return $this->results;
    }

    /**
     * @param array<array-key, mixed>|null $results
     */
    public function setResults(?array $results): static
    {
        $this->results = $results;

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

    /**
     * @return array<array-key, mixed>
     */
    public function getAwardJson(): array
    {
        return $this->awardJson ?? [];
    }

    /**
     * @param array<array-key, mixed>|null $awardJson
     */
    public function setAwardJson(?array $awardJson): Award
    {
        $this->awardJson = $awardJson;

        return $this;
    }

    public function getAwardEmail(): ?string
    {
        return $this->awardEmail;
    }

    public function setAwardEmail(?string $awardEmail): Award
    {
        $this->awardEmail = $awardEmail;

        return $this;
    }

    public function canDelete(): bool
    {
        return in_array($this->state, [AwardState::Pending, AwardState::Failed], true);
    }

    public function canEdit(): bool
    {
        return $this->state === AwardState::Pending;
    }

    public function canPublish(): bool
    {
        return $this->state === AwardState::Pending;
    }

    public function canRevoke(): bool
    {
        return !in_array($this->state, [AwardState::Pending, AwardState::Revoking, AwardState::Revoked, AwardState::Failed], true);
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function setRequestId(?string $requestId): Award
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    public function getLastResponse(): ?array
    {
        return $this->lastResponse;
    }

    /**
     * @param array<array-key, mixed>|null $lastResponse
     */
    public function setLastResponse(?array $lastResponse): Award
    {
        $this->lastResponse = $lastResponse;

        return $this;
    }

    public function getLastUpdated(): ?\DateTimeInterface
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(?\DateTimeInterface $lastUpdated): Award
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function getAwardEmailFrom(): ?string
    {
        return $this->awardEmailFrom;
    }

    public function setAwardEmailFrom(?string $awardEmailFrom): Award
    {
        $this->awardEmailFrom = $awardEmailFrom;

        return $this;
    }

    public function getAwardEmailSubject(): ?string
    {
        return $this->awardEmailSubject;
    }

    public function setAwardEmailSubject(?string $awardEmailSubject): Award
    {
        $this->awardEmailSubject = $awardEmailSubject;

        return $this;
    }

    public function getAcceptUrl(): ?string
    {
        return $this->acceptUrl;
    }

    public function setAcceptUrl(?string $acceptUrl): Award
    {
        $this->acceptUrl = $acceptUrl;

        return $this;
    }

    /**
     * @return Collection<int, EvidenceFile>
     */
    public function getEvidence(): Collection
    {
        return $this->evidence;
    }

    public function addEvidence(EvidenceFile $evidenceFile): static
    {
        if (!$this->evidence->contains($evidenceFile)) {
            $this->evidence->add($evidenceFile);
            $evidenceFile->setAward($this);
        }

        return $this;
    }

    public function removeEvidence(EvidenceFile $evidenceFile): static
    {
        // set the owning side to null (unless already changed)
        if ($this->evidence->removeElement($evidenceFile) && $evidenceFile->getAward() === $this) {
            $evidenceFile->setAward(null);
        }

        return $this;
    }

    public function getEvidenceFile(): ?EvidenceFile
    {
        $evidence = $this->evidence->first();

        if ($evidence instanceof EvidenceFile) {
            return $evidence;
        }

        return null;
    }

    public function setEvidenceFile(?EvidenceFile $evidenceFile): Award
    {
        $this->evidence->clear();
        if ($evidenceFile instanceof EvidenceFile) {
            $this->evidence->add($evidenceFile);
            $evidenceFile->setAward($this);
        }

        return $this;
    }

    public function getDateIssued(): ?\DateTimeInterface
    {
        if ($this->id instanceof UuidV7 || $this->id instanceof UuidV1) {
            return $this->id->getDateTime();
        }

        return null;
    }
}
