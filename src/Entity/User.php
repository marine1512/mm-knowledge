<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Entité représentant un utilisateur.
 */

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[UniqueEntity(fields: ['username'], message: 'Il existe déjà un compte avec cet identifiant.')]
 
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /** 
     * ID unique de l'utilisateur.
     * 
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /** 
     * Nom d'utilisateur unique.
     * 
     * @var string|null
     */

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    /** 
     * Adresse e-mail unique de l'utilisateur.
     * 
     * @var string|null
     */
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * Adresse de livraison.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $shippingAddress = null;

    /**
     * Rôles associés à l'utilisateur (format JSON).
     *
     * @var array
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * Mot de passe chiffré de l'utilisateur.
     * 
     * @var string|null
     */

    #[ORM\Column]
    private ?string $password = null;

    /** 
     * Indique si l'utilisateur est vérifié.
     *
     * @var bool
     */
    #[ORM\Column]
    private bool $isVerified = false;

    /** 
     * Jeton pour la vérification de l'adresse e-mail.
     * 
     * @var string|null
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $emailVerificationToken = null;

    /**
     * Retourne l'ID de l'utilisateur.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'identifiant unique de l'utilisateur (adresse e-mail).
     *
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->email; 
    }

    /**
     * Retourne le nom d'utilisateur.
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Définit le nom d'utilisateur.
     *
     * @param string $username
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Récupère l'adresse e-mail.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'adresse e-mail.
     * 
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Retourne l'adresse de livraison.
     * 
     * @return string|null
     */
    public function getShippingAddress(): ?string
    {
        return $this->shippingAddress;
    }

    /**
     * Définit l'adresse de livraison.
     *
     * @param string|null $shippingAddress
     * @return self
     */
    public function setShippingAddress(?string $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * Retourne les rôles associés à l'utilisateur.
     *
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Définit les rôles de l'utilisateur.
     *
     * @param array $roles
     * @return self
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Retourne le mot de passe chiffré.
     * 
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe.
     *
     * @param string $password
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Efface les informations sensibles.
     * 
     * @return void
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * Indique si l'utilisateur est vérifié.
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * Définit si l'utilisateur est vérifié.
     * 
     * @param bool $isVerified
     * @return self
     */
    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * Retourne le jeton de vérification de l'e-mail.
     * 
     * @return string|null
     */
    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    /**
     * Définit le jeton de vérification de l'e-mail.
     *
     * @param string|null $emailVerificationToken
     * @return self
     */
    public function setEmailVerificationToken(?string $emailVerificationToken): self
    {
        $this->emailVerificationToken = $emailVerificationToken;

        return $this;
    }
}