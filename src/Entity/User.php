<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use App\Validator\Constraints as CustomAssert;
use Doctrine\ORM\Mapping as ORM;
use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectInterface;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface, PersistenceSubjectInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[CustomAssert\PasswordField(options: ["message" => "Passwords must have at least 3 of: lowercase letter, uppercase letter, number, and symbol"])]
    #[Assert\NotBlank(groups: ['registration'])]
    #[Assert\Length(min: 8, max: 4096, minMessage: 'Password must be at least {{ limit }} characters long', maxMessage: 'Password cannot be longer than {{ limit }} characters')]
    private ?string $plainPassword = null;

    #[ORM\Column(nullable: true)]
    private ?string $totpSecret = null;

    #[ORM\Column(name: 'totp_enabled', nullable: true)]
    protected ?bool $isTotpEnabled = false;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    #[\Override]
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    #[\Override]
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $password): void
    {
        $this->plainPassword = $password;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    #[\Override]
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    #[\Override]
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function setTotpSecret(?string $totpSecret): static
    {
        $this->totpSecret = $totpSecret;

        return $this;
    }

    public function setIsTotpEnabled(bool $isTotpEnabled): void
    {
        $this->isTotpEnabled = $isTotpEnabled;
    }

    #[\Override]
    public function isTotpAuthenticationEnabled(): bool
    {
        return null !== $this->totpSecret && true === $this->isTotpEnabled;
    }

    #[\Override]
    public function getTotpAuthenticationUsername(): string
    {
        return $this->getUserIdentifier();
    }

    #[\Override]
    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        // Compatible with Google Authenticator
        return new TotpConfiguration($this->totpSecret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }

    #[\Override]
    public function getDataTablePersistenceIdentifier(): string
    {
        return $this->id->toBase58();
    }
}
