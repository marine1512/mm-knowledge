<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ThemeRepository;

/**
 * Contrôleur HomeController.
 *
 * Ce contrôleur est responsable de la gestion de la route "/home",
 * incluant l'affichage des themes et la vérification de
 * l'état de connexion de l'utilisateur pour personnaliser la vue.
 */
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ThemeRepository $themeRepository): Response
    {
        $themeProducts = $themeRepository->findAll();

        // Vérification si l'utilisateur est connecté
        $isUserLoggedIn = $this->isGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('home/index.html.twig', [
            'themeProducts' => $themeProducts,
            'isUserLoggedIn' => $isUserLoggedIn,
        ]);
    }
}