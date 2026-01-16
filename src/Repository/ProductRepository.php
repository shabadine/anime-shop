<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Pour la page d'accueil : récupère les produits les plus vendus
     */
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

    /**
     * Pour la page Catalogue : Gère la recherche, les filtres et le tri
     */
    public function findFilteredProductsQuery(?string $category, ?string $search, string $sortBy, string $order): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c');

        if ($category) {
            $qb->andWhere('p.category = :category')
               ->setParameter('category', $category);
        }

        if ($search) {
            $qb->andWhere('p.name LIKE :search OR p.animeName LIKE :search OR p.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        switch ($sortBy) {
            case 'price':
                $qb->orderBy('p.price', $order);
                break;
            case 'date':
                $qb->orderBy('p.createdAt', 'DESC');
                break;
            default:
                $qb->orderBy('p.name', $order);
        }

        return $qb; 
    }

    /**
     * Pour la page Catégorie (Listage simple)
     */
    public function findByCategoryQuery(Category $category): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->where('p.category = :category')
            ->setParameter('category', $category)
            ->orderBy('p.name', 'ASC');
    }

    /**
     * Pour la page Fiche Produit (Suggestions)
     */
    public function findSimilarProducts(Product $product, int $limit = 4): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.category = :category')
            ->andWhere('p.id != :productId')
            ->setParameter('category', $product->getCategory())
            ->setParameter('productId', $product->getId())
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Pour la page d'accueil : Produits mis en avant
     */
    public function findFeatured(int $limit = 8): array
    {
        return $this->findBy(['featured' => true], null, $limit);
    }

    /**
     * Pour la page d'accueil : Produits en promotion
     */
    public function findPromotions(int $limit = 4): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.pricePromotion IS NOT NULL')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}