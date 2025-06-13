<?php

namespace App\Service;

use App\Repository\CursusRepository;
use App\Repository\LeconRepository;
use App\Entity\Cursus;
use App\Entity\Lecon;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService 
{
    public function __construct(
        private RequestStack $requestStack,
        private CursusRepository $cursusRepository,
        private LeconRepository $leconRepository,
    ) {}

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    public function add(int $id, string $type): void
    {
        $cart = $this->getSession()->get('cart', []);
        $key = $type . '-' . $id;
        $cart[$key] = ($cart[$key] ?? 0) + 1;
        $this->getSession()->set('cart', $cart);
    }

    public function getFullCart(): array
    {
        $cart = $this->getSession()->get('cart', []);
        $fullCart = [];

        foreach ($cart as $key => $quantity) {
            [$type, $id] = explode('-', $key);

            if ($type === 'cursus') {
                $item = $this->cursusRepository->find($id);
            } elseif ($type === 'lecon') {
                $item = $this->leconRepository->find($id);
            } else {
                continue;
            }

            // Si l'objet est invalide ou introuvable, on supprime la clé du panier
            if ($item === null || !($item instanceof Cursus || $item instanceof Lecon)) {
                unset($cart[$key]);
                $this->getSession()->set('cart', $cart);
                continue;
            }

            $fullCart[] = [
                'item' => $item,
                'quantity' => $quantity,
            ];
        }

        return $fullCart;
    }

    public function clear(): void
    {
        $this->getSession()->remove('cart');
    }

    public function remove(string $key): void
    {
        $cart = $this->getSession()->get('cart', []);

        if (isset($cart[$key])) {
            unset($cart[$key]);
            $this->getSession()->set('cart', $cart);
        }
    }
}