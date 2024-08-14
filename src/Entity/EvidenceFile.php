<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EvidenceFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: EvidenceFileRepository::class)]
#[Vich\Uploadable()]
class EvidenceFile
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?Uuid $id;

    #[Vich\UploadableField(mapping: 'evidence', fileNameProperty: 'name', size: 'size', mimeType: 'mimetype', originalName: 'originalName', dimensions: 'dimensions')]
    private ?File $file = null;

    #[ORM\Column(nullable: true)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $size = null;

    #[ORM\Column(nullable: true)]
    private ?string $mimetype = null;

    #[ORM\Column(nullable: true)]
    private ?string $originalName = null;

    #[ORM\Column(nullable: true)]
    private ?string $dimensions = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'evidence')]
    private ?Award $award = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): static
    {
        $this->file = $file;

        $this->updatedAt = new \DateTimeImmutable();

        if (!$file instanceof File) {
            $this->award->setEvidenceFile(null);
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getMimetype(): ?string
    {
        return $this->mimetype;
    }

    public function setMimetype(?string $mimetype): static
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): static
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * @return array<array-key, int>|null
     */
    public function getDimensions(): ?array
    {
        return json_decode((string) $this->dimensions, true);
    }

    /**
     * @param array<array-key, int>|null $dimensions
     */
    public function setDimensions(?array $dimensions): static
    {
        $this->dimensions = json_encode($dimensions, JSON_THROW_ON_ERROR);

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
}
