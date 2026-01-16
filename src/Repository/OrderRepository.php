<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

     public function getTotalRevenue(): float
{
    $result = $this->createQueryBuilder('o')
        ->select('SUM(o.totalAmount)')
        ->where('o.status != :cancelled')
        ->setParameter('cancelled', 'annulee')
        ->getQuery()
        ->getSingleScalarResult();

    return $result ?? 0;
}
   public function findTopSelling(int $max = 5): array
{
    return $this->createQueryBuilder('o')
        ->select('p as product', 'SUM(oi.quantity) as qty')
        ->join('o.orderItems', 'oi') // Correction du nom ici
        ->join('oi.product', 'p')
        ->groupBy('p.id')
        ->orderBy('qty', 'DESC')
        ->setMaxResults($max)
        ->getQuery()
        ->getResult();
}

}
