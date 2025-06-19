<?php

namespace App\Controller;

use App\Entity\Theme;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class CertificationController extends AbstractController
{
    #[Route('/certification', name: 'certification', methods: ['GET'])]
public function showCertification(EntityManagerInterface $entityManager): Response
{
    $themes = $entityManager->getRepository(Theme::class)->findAll();

    if (!$themes) {
        throw $this->createNotFoundException('Aucun thème disponible.');
    }

    $certifications = [];
    foreach ($themes as $theme) {
        if ($theme->getCertification()) {
            $certifications[] = $theme->getCertification();
        }
    }

    return $this->render('certification/certification.html.twig', [
        'certifications' => $certifications,
    ]);
}
}