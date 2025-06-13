<?php

namespace App\Controller;

use App\Entity\Cursus;
use App\Entity\Lecon;
use App\Service\CartService;
use App\Service\StripeService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour la gestion du panier et du processus d'achat
 * avec prise en charge des Leçons et des Cursus.
 */
class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Récupération du panier en session
        $cart = $request->getSession()->get('cart', []);

        // Calcul du prix total
        $totalPrice = array_reduce($cart, function ($total, $item) {
            return $total + $item['prix'] * $item['quantité'];
        }, 0);

        return $this->render('panier/index.html.twig', [
            'cart' => $cart,
            'totalPrice' => $totalPrice,
        ]);
    }

    #[Route('/cart/add/lecon/{id}', name: 'cart_add_lecon', methods: ['POST'])]
    public function addLeconToCart($id, Request $request, ManagerRegistry $doctrine): Response
    {
        // Récupérer la Leçon depuis la base de données
        $lecon = $doctrine->getRepository(Lecon::class)->find($id);

        if (!$lecon) {
            $this->addFlash('error', 'Leçon non trouvée.');
            return $this->redirectToRoute('lecon_list');
        }

        // Gestion du panier
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        $itemKey = 'lecon-' . $id;

        // Ajouter ou mettre à jour la quantité
        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantité'] += 1;
        } else {
            $cart[$itemKey] = [
                'id' => $lecon->getId(),
                'nom' => $lecon->getNom(),
                'prix' => $lecon->getPrix(),
                'quantité' => 1,
            ];
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Leçon ajoutée au panier avec succès.');
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/add/cursus/{id}', name: 'cart_add_cursus', methods: ['POST'])]
    public function addCursusToCart($id, Request $request, ManagerRegistry $doctrine): Response
    {
        // Récupérer le Cursus depuis la base de données
        $cursus = $doctrine->getRepository(Cursus::class)->find($id);

        if (!$cursus) {
            $this->addFlash('error', 'Cursus non trouvé.');
            return $this->redirectToRoute('cursus_list');
        }

        // Gestion du panier
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        $itemKey = 'cursus-' . $id;

        // Ajouter ou mettre à jour la quantité
        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantité'] += 1;
        } else {
            $cart[$itemKey] = [
                'id' => $cursus->getId(),
                'nom' => $cursus->getNom(),
                'prix' => $cursus->getPrix(),
                'quantité' => 1,
            ];
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Cursus ajouté au panier avec succès.');
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove', methods: ['POST'])]
    public function removeFromCart($id, Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);
        $this->addFlash('success', 'Produit retiré du panier avec succès.');

        return $this->redirectToRoute('cart');
    }

     #[Route('/checkout', name: 'app_checkout')]
    public function checkout(CartService $cartService, StripeService $stripeService): Response
    {
        $cart = $cartService->getFullCart();

        // Si le panier est vide, rediriger l'utilisateur
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('cart');
        }

        $cartItems = [];
        foreach ($cart as $item) {
            if (!isset($item['item']) || !method_exists($item['item'], 'getNom') || !method_exists($item['item'], 'getPrix')) {
                throw new \LogicException("Le panier contient un élément invalide.");
            }

            $cartItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['item']->getNom(),
                    ],
                    'unit_amount' => (int) ($item['item']->getPrix() * 100),
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $session = $stripeService->createCheckoutSession(
            $cartItems,
            $this->generateUrl('payment_success', [], false),
            $this->generateUrl('payment_cancel', [], false)
        );

        return $this->redirect($session->url, 303); // Redirection vers la page Stripe
    }

    #[Route('/success', name: 'payment_success')]
    public function success(CartService $cartService): Response
    {
        $cartService->clear();
        $this->addFlash('success', 'Votre paiement a été effectué avec succès !');
        return $this->render('panier/success.html.twig', [
            'message' => 'Merci pour votre achat ! Votre paiement a été effectué avec succès.',
        ]);
    }

    #[Route('/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Votre paiement a été annulé.');
        return $this->redirectToRoute('cart');
    }
}