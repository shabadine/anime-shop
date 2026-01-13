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

    public function add(int $id, int $quantity = 1): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + $quantity;
        $session->set('cart', $cart);
    }

    public function remove(int $id, int $quantity = 1): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        if (!isset($cart[$id])) return;

        $cart[$id] -= $quantity;
        if ($cart[$id] <= 0) unset($cart[$id]);

        $session->set('cart', $cart);
    }

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

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getFullCart() as $item) {
            $product = $item['product'];
            $price = $product->getPricePromotion() ?: $product->getPrice();
            $total += $price * $item['quantity'];
        }
        return $total;
    }

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

    public function clear(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->hasSession()) {
            $this->requestStack->getSession()->remove('cart');
        }
    }
}