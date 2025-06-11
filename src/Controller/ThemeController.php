<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ThemeRepository;
use App\Repository\CursusRepository;

/**
 * Contrôleur pour gérer la boutique en ligne.
 *
 * Ce contrôleur gère la liste des produits et les détails d'un produit individuel.
 */
class ThemeController extends AbstractController
{

    /**
     * Affiche les détails d'un produit spécifique.
     *
     * Si le produit n'est pas trouvé, une exception HTTP 404 est levée.
     *
     * @Route("/product/{id}", name="product_detail", methods={"GET"})
     *
     * @param int $id L'identifiant unique du produit à afficher.
     * @param SweatshirtRepository $productRepository Le repository permettant de trouver les produits.
     *
     * @return Response La réponse HTTP contenant la vue des détails du produit.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si le produit n'est pas trouvé.
     */
    #[Route('/product/{id}', name: 'product_detail', methods: ['GET'])]
    public function detail(int $id, ThemeRepository $themeRepository): Response
    {
        $theme = $themeRepository->find($id);

        if (!$theme) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        return $this->render('formations/theme-detail.html.twig', [
            'theme' => $theme,
            'cursus' => $theme->getCursus(), // Ici, getCursus() doit renvoyer une Collection ou un tableau
        ]);
    }

    #[Route('/cursus/{id}/lecon', name: 'cursus_lecon', methods: ['GET'])]
    public function lessons(int $id, CursusRepository $cursusRepository): Response
    {
        $cursus = $cursusRepository->find($id);

        if (!$cursus) {
            throw $this->createNotFoundException('Cursus non trouvé');
        }

        // Récupérer les leçons associées
        $lecons = $cursus->getLecons();

        return $this->render('formations/cursus-detail.html.twig', [
            'cursus' => $cursus,
            'lecons' => $lecons,  // Les leçons à envoyer au template
        ]);
    }
}