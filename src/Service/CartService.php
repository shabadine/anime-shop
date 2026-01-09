<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $requestStack;
    private $productRepository;

    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }

    /**
     * Ajoute $quantity exemplaires du produit $id
    */
    public function add(int $id, int $quantity = 1): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + $quantity;
        $session->set('cart', $cart);
    }

    /**
     * Retire $quantity exemplaires du produit $id (supprime la ligne si qty <= 0)
     */
    public function remove(int $id, int $quantity = 1): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        if (!isset($cart[$id])) return;

        $cart[$id] -= $quantity;
        if ($cart[$id] <= 0) unset($cart[$id]);

        $session->set('cart', $cart);
    }

    /**
     * Récupère le panier détaillé avec les objets Produits depuis la BDD
     */
    public function getFullCart(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request || !$request->hasSession()) {
            return [];
        }

        $cart = $this->requestStack->getSession()->get('cart', []);
        $cartDetailed = [];

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            if ($product) {
                $cartDetailed[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartDetailed;
    }
    /**
    * Met à jour la quantité d’un produit dans le panier
    */
    public function updateQuantity(int $id, int $quantity): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if ($quantity <= 0) {
            unset($cart[$id]);
        } else {
            $cart[$id] = $quantity;
        }

        $session->set('cart', $cart);
    }

    /**
     * Calcule le montant total financier du panier
     */
    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getFullCart() as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }
        return $total;
    }

    /**
     * Retourne le nombre total d'articles (somme des quantités)
     */
    public function getItemCount(): int
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request || !$request->hasSession()) {
            return 0;
        }

        try {
            $cart = $this->requestStack->getSession()->get('cart', []);
            $count = 0;
            foreach ($cart as $quantity) {
                $count += $quantity;
            }
            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Vide complètement le panier en session
     *
     */
    public function clear(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->hasSession()) {
            $this->requestStack->getSession()->remove('cart');
        }
    }
}