<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserPurchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'purchases')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Lecon::class)] // Vérifiez le nom correct ici
    private ?Lecon $lecon = null; // Ou la classe Leçon si le nom est différent

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLecon(): ?Lecon
    {
        return $this->lecon;
    }

    public function setLecon(?Lecon $lecon): self
    {
        $this->lecon = $lecon;

        return $this;
    }
}