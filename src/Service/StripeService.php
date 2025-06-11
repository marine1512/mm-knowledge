<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

/**
 * Service pour l'intégration avec l'API Stripe.
 *
 * Cette classe permet de gérer les interactions avec Stripe,
 * y compris la création de sessions de paiement.
 */
class StripeService
{
    /**
     * Initialise le service Stripe avec la clé secrète API.
     *
     * @param string $stripeApiSecret Clé secrète de l'API Stripe.
     */
    public function __construct(
        private string $stripeApiSecret,
    ) {
        Stripe::setApiKey($this->stripeApiSecret);
    }

    /**
     * Crée une session Stripe Checkout.
     *
     * Cette méthode construit les `line_items` à partir des éléments du panier
     * et génère une session de paiement Stripe.
     *
     * @param array $cartItems Liste des produits dans le panier. Exemple d'élément :
     * [
     *     'price_data' => [
     *         'currency' => 'usd',
     *         'product_data' => [
     *             'name' => 'Nom du produit'
     *         ],
     *         'unit_amount' => 1000,
     *     ],
     *     'quantity' => 2
     * ]
     * @param string $successUrl URL à laquelle l'utilisateur sera redirigé après un paiement réussi.
     * @param string $cancelUrl URL à laquelle l'utilisateur sera redirigé en cas d'annulation du paiement.
     *
     * @return StripeSession La session de paiement créée.
     *
     * @throws \InvalidArgumentException Si les informations des produits (cartItems) sont invalides.
     * @throws \Stripe\Exception\ApiErrorException Si l'API Stripe retourne une erreur.
     */
    public function createCheckoutSession(
        array $cartItems,
        string $successUrl,
        string $cancelUrl
    ): StripeSession {
        $lineItems = [];
    
        foreach ($cartItems as $cartItem) {
            if (!isset($cartItem['price_data'], $cartItem['quantity'])) {
                throw new \InvalidArgumentException('Les informations du produit sont invalides');
            }
    
            $priceData = $cartItem['price_data'];
            $lineItems[] = [
                'price_data' => [
                    'currency' => $priceData['currency'],
                    'product_data' => [
                        'name' => $priceData['product_data']['name'], 
                    ],
                    'unit_amount' => (int) $priceData['unit_amount'],
                ],
                'quantity' => (int) $cartItem['quantity'], 
            ];
        }
    
        // Crée une session Stripe
        return StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }
}