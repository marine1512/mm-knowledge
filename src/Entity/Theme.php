<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Certification;

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

    #[ORM\Column(type: "boolean")]
       private bool $valide = false;

    #[ORM\OneToOne(mappedBy: 'theme', targetEntity: Certification::class, cascade: ['persist', 'remove'])]
    private ?Certification $certification = null;

    public function __construct()
    {
        $this->cursus = new ArrayCollection();
        $this->valide = false; 
        $this->certification = null;
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

    public function isValide(): bool
{
    return $this->valide;
}

public function setValide(bool $valide): self
{
    $this->valide = $valide;

    return $this;
}

public function getCertification(): ?Certification
{
    return $this->certification;
}

public function setCertification(?Certification $certification): self
{
    // Gérer la relation bidirectionnelle
    if ($certification && $certification->getTheme() !== $this) {
        $certification->setTheme($this);
    }

    $this->certification = $certification;

    return $this;
}
}
