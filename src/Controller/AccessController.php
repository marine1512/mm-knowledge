<?php

namespace App\Controller;

use App\Repository\LeconRepository;
use App\Repository\CursusRepository;
use App\Repository\UserPurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccessController extends AbstractController
{
    private $leconRepository;
    private $cursusRepository;
    private $userPurchaseRepository;

    public function __construct(LeconRepository $leconRepository, UserPurchaseRepository $userPurchaseRepository, CursusRepository $cursusRepository)
    {
        $this->leconRepository = $leconRepository;
        $this->userPurchaseRepository = $userPurchaseRepository;
        $this->cursusRepository = $cursusRepository;
    }


    #[Route('/mes-achats', name: 'user_achat', methods: ['GET'])]
    public function accessPurchasedCursus(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos cursus.');
            return $this->redirectToRoute('app_login');
        }

        $cursus = $this->userPurchaseRepository->getPurchasedCursus($user);
        $lecon = $this->userPurchaseRepository->getPurchasedLecons($user);
        if (empty($cursus) && empty($lecon)) {
            $this->addFlash('info', 'Vous n’avez encore acheté aucun cursus.');
            return $this->redirectToRoute('home');
        }

        return $this->render('cours_payes/achat.html.twig', [
            'cursus' => $cursus,
            'lecon' => $lecon
        ]);
    }

    #[Route('/mes-achats/cursus/{id}', name: 'contenu_cursus', methods: ['GET'])]
    public function detailCursus(int $id, CursusRepository $cursusRepository): Response
    {
        $cursus = $cursusRepository->find($id);

        if (!$cursus) {
            throw $this->createNotFoundException('Cursus non trouvé');
        }

        return $this->render('cours_payes/cursus_contenu.html.twig', [
            'cursus' => $cursus
        ]);
    }

    #[Route('/mes-achats/lecon/{id}', name: 'contenu_lecon', methods: ['GET'])]
    public function detailLecon(int $id, LeconRepository $leconRepository): Response
    {
        $lecon = $leconRepository->find($id);

        if (!$lecon) {
            throw $this->createNotFoundException('Leçon non trouvée');
        }

        return $this->render('cours_payes/lecon_contenu.html.twig', [
            'lecon' => $lecon
        ]);
    }

}