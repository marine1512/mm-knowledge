<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)] // Permet de rendre ce champ optionnel
    private ?string $image = null;
    #[ORM\OneToMany(mappedBy: 'theme', targetEntity: Cursus::class)]
    private Collection $cursus;

    public function __construct()
    {
        $this->cursus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCursus(): Collection
    {
        return $this->cursus;
    }

    public function addCursus(Cursus $cursus): self
    {
        if (!$this->cursus->contains($cursus)) {
            $this->cursus->add($cursus);
            $cursus->setTheme($this);
        }

        return $this;
    }

    public function removeCursus(Cursus $cursus): self
    {
        if ($this->cursus->removeElement($cursus)) {
            // Set the owning side to null (unless already changed)
            if ($cursus->getTheme() === $this) {
                $cursus->setTheme(null);
            }
        }

        return $this;
    }
}
