<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ThemeRepository;
use App\Entity\Theme;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ThemeRepository $themeRepository): Response
    {
        $themeProducts = $themeRepository->findAll();

        $isUserLoggedIn = $this->isGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('home/index.html.twig', [
            'themeProducts' => $themeProducts,
            'isUserLoggedIn' => $isUserLoggedIn,
        ]);
    }
}