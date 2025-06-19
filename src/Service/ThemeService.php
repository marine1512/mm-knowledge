<?php

namespace App\Service;

use App\Entity\Theme;
use App\Entity\Certification;
use Doctrine\ORM\EntityManagerInterface;

class ThemeService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Valide un thème et crée une certification s'il n'en a pas déjà une.
     * 
     * @param Theme $theme
     * @return bool True si une certification a été créée, False sinon.
     */
    public function validerTheme(Theme $theme): bool
    {
        if (!$theme) {
            throw new \InvalidArgumentException('Le thème fourni est nul ou invalide.');
        }

        // Si le thème est déjà valide, rien à faire
        if ($theme->isValide()) {
            return false;
        }

        // Marquer le thème comme validé
        $theme->setValide(true);

        // Créer une certification uniquement si elle n'existe pas déjà
        $certificationCree = false;
        if (!$theme->getCertification()) {
            $certification = new Certification();
            $certification->setTheme($theme);
            $certification->setCreatedAt(new \DateTimeImmutable());

            // Persister la certification
            $this->entityManager->persist($certification);
            $certificationCree = true;
        }

        // Sauvegarder les modifications
        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Échec lors de la validation du thème : ' . $e->getMessage());
        }

        return $certificationCree;
    }
}