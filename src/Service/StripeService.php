<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

/**
 * Service pour l'intégration avec l'API Stripe.
 */
class StripeService
{
    /**
     * @var string
     */
    private string $stripeApiSecret;

    /**
     * Initialise le service Stripe avec la clé secrète API.
     *
     * @param string $stripeApiSecret
     */
    public function __construct(string $stripeApiSecret)
    {
        $this->stripeApiSecret = $stripeApiSecret;
        Stripe::setApiKey($this->stripeApiSecret);
    }

    /**
     * Crée une session Stripe Checkout.
     *
     * @param array $cartItems Liste des produits dans le panier.
     * @param string $successUrl URL après le succès du paiement.
     * @param string $cancelUrl URL en cas d'annulation.
     *
     * @return StripeSession La session de paiement Stripe.
     *
     * @throws \InvalidArgumentException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createCheckoutSession(array $cartItems, string $successUrl, string $cancelUrl): StripeSession
    {
        $lineItems = [];

        foreach ($cartItems as $cartItem) {
            if (!isset($cartItem['price_data'], $cartItem['quantity'])) {
                throw new \InvalidArgumentException('Les informations du produit sont invalides');
            }

            $priceData = $cartItem['price_data'];
            $lineItems[] = [
                'price_data' => [
                    'currency' => $priceData['currency'], // Devrait correspondre à "eur" ou "usd"
                    'product_data' => [
                        'name' => $priceData['product_data']['name'],
                    ],
                    'unit_amount' => (int) $priceData['unit_amount'], // Exprimé en centimes
                ],
                'quantity' => (int) $cartItem['quantity'], // Quantité demandée
            ];
        }

        // Créer la session Stripe avec les `line_items`
        return StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }
}