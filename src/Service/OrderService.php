<?php
namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CartService $cartService
    ) {}

    public function createOrder(User $user): Order
    {
        $items = $this->cartService->getFullCart();
        
        if (empty($items)) {
            throw new \RuntimeException('Le panier est vide.');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setTotalAmount($this->cartService->getTotal());
        $order->setStatus('en_attente'); 

        foreach ($items as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];

            if (!$this->cartService->hasEnoughStock($product, $quantity)) {
                throw new \RuntimeException('Stock insuffisant pour le produit : ' . $product->getName());
            }
            
            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            
           
            $price = $product->getPricePromotion() ?: $product->getPrice();
            $orderItem->setUnitPrice($price);
            
            $order->addOrderItem($orderItem);
            
            
            $product->setStock($product->getStock() - $quantity);
        }

        $this->em->persist($order);
        $this->em->flush();

        $this->cartService->clear();

        return $order;
    }
}