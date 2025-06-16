<?php

namespace App\Controller;

use App\Repository\LeconRepository;
use App\Repository\UserPurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccessController extends AbstractController
{
    private $leconRepository;
    private $userPurchaseRepository;

    public function __construct(LeconRepository $leconRepository, UserPurchaseRepository $userPurchaseRepository)
    {
        $this->leconRepository = $leconRepository;
        $this->userPurchaseRepository = $userPurchaseRepository;
    }

    #[Route('/mes-lecons', name: 'user_lecons', methods: ['GET'])]
public function accessPurchasedLecons(): Response
{
    // Récupérer l'utilisateur connecté
    $user = $this->getUser();
    if (!$user) {
        $this->addFlash('error', 'Vous devez être connecté pour voir vos leçons.');
        return $this->redirectToRoute('app_login');
    }

    // Récupérer toutes les leçons achetées par l'utilisateur
    $lecons = $this->userPurchaseRepository->getPurchasedLecons($user);

    if (empty($lecons)) {
        $this->addFlash('info', 'Vous n’avez encore acheté aucune leçon.');
        return $this->redirectToRoute('home'); // Redirection vers l'accueil ou autre page
    }

    // Afficher les leçons achetées
    return $this->render('cours_payes/lecon.html.twig', [
        'lecons' => $lecons,
    ]);
}
}