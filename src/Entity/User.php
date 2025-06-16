<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Entity\Lecon; 
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = false;

    #[ORM\ManyToMany(targetEntity: Lecon::class, inversedBy: 'users')]
    private Collection $lecons;

    #[ORM\ManyToMany(targetEntity: Cursus::class, inversedBy: 'users')]
    private Collection $cursus;

    /** 
     * Jeton pour la vérification de l'adresse e-mail.
     * 
     * @var string|null
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $emailVerificationToken = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserPurchase::class, cascade: ['persist', 'remove'])]
    private Collection $purchases;

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

        public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function __construct()
    {
        $this->lecons = new ArrayCollection();
        $this->cursus = new ArrayCollection(); // Ajout
        $this->purchases = new ArrayCollection();
    }

    /**
     * @return Collection<int, Lecon>
     */
    public function getLecons(): Collection
    {
        return $this->lecons;
    }

    public function addLecon(Lecon $lecon): self
    {
        if (!$this->lecons->contains($lecon)) {
            $this->lecons->add($lecon); // Ajout à la collection
        }

        return $this;
    }

    public function removeLecon(Lecon $lecon): self
    {
        $this->lecons->removeElement($lecon);

        return $this;
    }

    public function getCursus(): Collection
    {
        return $this->cursus;
    }

    public function addCursus(Cursus $cursus): self
    {
        if (!$this->cursus->contains($cursus)) {
            $this->cursus->add($cursus); // Ajout à la collection
        }

        return $this;
    }

    public function removeCursus(Cursus $cursus): self
    {
        $this->cursus->removeElement($cursus);

        return $this;
    }

    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(UserPurchase $purchase): self
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setUser($this);
        }

        return $this;
    }

    public function removePurchase(UserPurchase $purchase): self
    {
        if ($this->purchases->removeElement($purchase)) {
            // Set the owning side to null (unless already changed)
            if ($purchase->getUser() === $this) {
                $purchase->setUser(null);
            }
        }

        return $this;
    }
}