<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enums\ParticipantState;
use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\UniqueConstraint(columns: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'This email address is already used.')]
class Participant
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\ManyToOne(targetEntity: Pathway::class)]
    private ?Pathway $subscribedPathway = null;

    #[ORM\Column(nullable: true)]
    private ?bool $acceptedTerms = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $aboutMe = null;

    #[ORM\Column(length: 255)]
    private ?ParticipantState $state = ParticipantState::Active;

    /** @var Collection<array-key, Award> $awards */
    #[ORM\OneToMany(targetEntity: Award::class, mappedBy: 'subject')]
    private Collection $awards;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->awards = new ArrayCollection();
    }

    /**
     * @param array<array-key, mixed> $rec
     */
    public static function fromCsv(array $rec): self
    {
        $participant = new self();
        $participant
            ->setFirstName($rec['firstName'])
            ->setLastName($rec['lastName'])
            ->setEmail($rec['email'])
            ->setSubscribedPathway($rec['pathway'])
            ->setAcceptedTerms(in_array(strtoupper((string) $rec['acceptedTerms']), ['Y', 'YES'], true))
            ->setPhone($rec['phone'] ?? null)
            ->setAboutMe($rec['aboutMe'] ?? null)
            ;

        if ($participant->isAcceptedTerms() !== true) {
            $participant->setState(ParticipantState::Suspended);
        }

        return $participant;
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSubscribedPathway(): ?Pathway
    {
        return $this->subscribedPathway;
    }

    public function setSubscribedPathway(?Pathway $subscribedPathway): static
    {
        $this->subscribedPathway = $subscribedPathway;

        return $this;
    }

    public function isAcceptedTerms(): ?bool
    {
        return $this->acceptedTerms;
    }

    public function setAcceptedTerms(?bool $acceptedTerms): static
    {
        $this->acceptedTerms = $acceptedTerms;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAboutMe(): ?string
    {
        return $this->aboutMe;
    }

    public function setAboutMe(?string $aboutMe): static
    {
        $this->aboutMe = $aboutMe;

        return $this;
    }

    public function getState(): ?ParticipantState
    {
        return $this->state;
    }

    public function setState(ParticipantState $state): static
    {
        $this->state = $state;

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
            $award->setSubject($this);
        }

        return $this;
    }

    public function canDelete(): bool
    {
        return $this->awards->count() === 0;
    }
}
