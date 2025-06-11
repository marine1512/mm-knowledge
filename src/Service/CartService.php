<?php

namespace App\Service;

use App\Repository\SweatshirtRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Service pour gérer le panier de l'application.
 *
 * Ce service offre des fonctionnalités pour ajouter, retirer, 
 * vider le panier et consulter le contenu complet du panier.
 */
class CartService 
{
    /**
     * Constructeur pour initialiser le service du panier.
     *
     * @param RequestStack $requestStack Permet de gérer les sessions via la requête.
     * @param SweatshirtRepository $sweatshirtRepository Repository pour récupérer les produits (sweatshirts).
     */
    public function __construct(
        private RequestStack $requestStack,
        private SweatshirtRepository $sweatshirtRepository,
    ) {}

    /**
     * Récupère la session courante à partir de la requête.
     *
     * @return SessionInterface La session courante.
     */
    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    /**
     * Ajoute un produit au panier.
     *
     * @param int $id L'identifiant unique du produit.
     * @return void
     */
    public function add(int $id): void
    {
        $cart = $this->getSession()->get('cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + 1;
        $this->getSession()->set('cart', $cart);
    }

    /**
     * Récupère le contenu complet du panier avec les détails des produits.
     *
     * @return array Un tableau contenant les produits et leurs quantités.
     *               Chaque élément contient :
     *               - `product` : L'entité du produit.
     *               - `quantity` : La quantité du produit dans le panier.
     */
    public function getFullCart(): array
    {
        $cart = $this->getSession()->get('cart', []);
        $fullCart = [];

        foreach ($cart as $id => $quantity) {
            $product = $this->sweatshirtRepository->find($id);
            if ($product) {
                $fullCart[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];
            }
        }
        return $fullCart;
    }

    /**
     * Vider complètement le panier.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->getSession()->remove('cart');
    }

    /**
     * Retire un produit du panier.
     *
     * @param int $id Identifiant unique du produit à retirer.
     * @return void
     */
    public function remove(int $id): void
    {
        $cart = $this->getSession()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            $this->getSession()->set('cart', $cart);
        }
    }
}