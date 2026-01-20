<?php

namespace App\Twig\Extension;

use App\Service\CartService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CartExtension extends AbstractExtension
{
    private $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function getFunctions(): array
    {
        return [
            // cette ligne qui définit le nom utilisable dans Twig
            new TwigFunction('cart_item_count', [$this, 'getItemCount']),
        ];
    }

    /**
     * Retourne le nombre d'articles en vérifiant l'existence d'une session
     */
    public function getItemCount(): int
    {
        try {
            return $this->cartService->getItemCount();
        } catch (\Exception $e) {
            // En cas d'absence de session ou d'erreur de service, on retourne 0
            return 0;
        }
    }
}