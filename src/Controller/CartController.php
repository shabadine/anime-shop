<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index')]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
        ]);
    }

    #[Route('/ajouter/{id}', name: 'app_cart_add')]
    public function add(int $id, Request $request, CartService $cartService, ProductRepository $productRepository): Response 
    {
        $product = $productRepository->find($id);
        $quantity = $request->request->getInt('quantity', 1);

        if (!$product) {
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('app_catalog');
        }

        if (!$cartService->hasEnoughStock($product, $quantity)) {
            $this->addFlash('error', 'Stock insuffisant');
            return $this->redirectToRoute('app_product_show', ['slug' => $product->getSlug()]);
        }

        $cartService->add($id, $quantity);
        $this->addFlash('success', 'Produit ajouté au panier !');

        return $this->redirect($request->headers->get('referer', $this->generateUrl('app_catalog')));
    }

    #[Route('/supprimer/{id}', name: 'app_cart_remove')]
    public function remove(int $id, CartService $cartService): Response
    {
        $cartService->remove($id);
        $this->addFlash('success', 'Produit retiré du panier');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/modifier/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(int $id, Request $request, CartService $cartService, ProductRepository $productRepository): Response 
    {
        $quantity = $request->request->getInt('quantity', 1);
        $product = $productRepository->find($id);

        if ($product && $cartService->hasEnoughStock($product, $quantity)) {
            $cartService->updateQuantity($id, $quantity);
            $this->addFlash('success', 'Quantité mise à jour');
        } else {
            $this->addFlash('error', 'Stock insuffisant ou produit invalide');
        }

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/vider', name: 'app_cart_clear')]
    public function clear(CartService $cartService): Response
    {
        $cartService->clear();
        $this->addFlash('success', 'Panier vidé');
        return $this->redirectToRoute('app_cart_index');
    }
}