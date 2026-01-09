<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

  public function findTopSelling(int $limit = 5): array
{
    return $this->createQueryBuilder('p')
        ->join('p.orderItems', 'oi')
        ->select('p, SUM(oi.quantity) AS totalSold')
        ->groupBy('p.id')
        ->orderBy('totalSold', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}

}
