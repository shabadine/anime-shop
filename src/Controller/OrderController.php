<?php

namespace App\Controller;


use App\Entity\User;
use App\Service\CartService;
use App\Service\OrderService;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commande')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    #[Route('/preparer', name: 'app_order_prepare')]
    public function prepare(CartService $cartService): Response
    {
        $items = $cartService->getFullCart();

        if (empty($items)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart_index');
        }

        /** @var User $user */
        $user = $this->getUser();
        $addresses = $user->getAddresses();

        return $this->render('order/prepare.html.twig', [
            'items' => $items,
            'total' => $cartService->getTotal(),
            'addresses' => $addresses,
        ]);
    }

    #[Route('/paiement', name: 'app_order_payment')]
    public function payment(CartService $cartService): Response
    {
        $items = $cartService->getFullCart();

        if (empty($items)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart_index');
        }

        return $this->render('order/payment.html.twig', [
            'items' => $items,
            'total' => $cartService->getTotal(),
        ]);
    }

    #[Route('/confirmer', name: 'app_order_confirm', methods: ['POST'])]
    public function confirm(
        OrderService $orderService, 
        CartService $cartService
    ): Response {
        if (empty($cartService->getFullCart())) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart_index');
        }

      
        $order = $orderService->createOrder($this->getUser());

        $this->addFlash('success', 'Commande validée avec succès !');

        return $this->redirectToRoute('app_order_success', [
            'orderNumber' => $order->getOrderNumber()
        ]);
    }

   #[Route('/succes/{orderNumber}', name: 'app_order_success')]
    public function success(string $orderNumber, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande introuvable');
        }

        return $this->render('order/success.html.twig', [
            'order' => $order,
        ]);
    }
}