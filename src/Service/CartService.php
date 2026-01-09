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