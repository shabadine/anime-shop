<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
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

        // Vérifier les adresses de l'utilisateur
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
        CartService $cartService,
        EntityManagerInterface $em
    ): Response {
        $items = $cartService->getFullCart();

        if (empty($items)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart_index');
        }

        // Créer la commande
        $order = new Order();
        $order->setUser($this->getUser());
        $order->setTotalAmount($cartService->getTotal() + 5); // + frais de port
        $order->setStatus('en_attente');

        // Ajouter les items
        foreach ($items as $item) {
            $orderItem = new OrderItem();
            $orderItem->setProduct($item['product']);
            $orderItem->setQuantity($item['quantity']);
            $orderItem->setUnitPrice($item['product']->getPrice());
            $orderItem->setOrder($order);

            $em->persist($orderItem);

            // Décrémenter le stock
            $product = $item['product'];
            $product->setStock($product->getStock() - $item['quantity']);
        }

        $em->persist($order);
        $em->flush();

        // Vider le panier
        $cartService->clear();

        $this->addFlash('success', 'Commande validée avec succès !');

        return $this->redirectToRoute('app_order_success', ['orderNumber' => $order->getOrderNumber()]);
    }

    #[Route('/succes/{orderNumber}', name: 'app_order_success')]
    public function success(string $orderNumber, EntityManagerInterface $em): Response
    {
        $order = $em->getRepository(Order::class)->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande introuvable');
        }

        return $this->render('order/success.html.twig', [
            'order' => $order,
        ]);
    }
}