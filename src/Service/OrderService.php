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
        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setOrderNumber(uniqid('CMD-'));
        $order->setStatus('en_attente'); 
        $order->setTotalAmount($this->cartService->getTotal());

        foreach ($this->cartService->getFullCart() as $item) {
            $product = $item['product'];
            
            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($item['quantity']);
            
            $price = $product->getPricePromotion() ?? $product->getPrice();
            $orderItem->setUnitPrice($price);
            
            $order->addOrderItem($orderItem);
            $this->em->persist($orderItem);
        }

        $this->em->persist($order);
        $this->em->flush();

        $this->cartService->clear();

        return $order;
    }
}