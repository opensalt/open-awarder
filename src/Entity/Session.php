<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'app_session')]
#[ORM\Index(name: 'session_sess_lifetime_idx', columns: ['sess_lifetime'])]
#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Column(name: 'sess_id', length: 128)]
    #[ORM\Id]
    private mixed $id;

    #[ORM\Column(name: 'sess_data', type: Types::BLOB)]
    private mixed $data;

    #[ORM\Column(name: 'sess_time')]
    private int $lastUsed;

    #[ORM\Column(name: 'sess_lifetime')]
    private int $lifetime;

    public function getId(): string
    {
        /** @phpstan-ignore-next-line */
        if (is_resource($this->id)) {
            $this->id = stream_get_contents($this->id);
        }

        return $this->id;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getLastUsed(): int
    {
        return $this->lastUsed;
    }

    public function getLastUsedTime(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('U', (string) $this->getLastUsed());
    }

    public function getLifetime(): int
    {
        return $this->lifetime;
    }
}
