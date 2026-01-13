<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

class CatalogController extends AbstractController
{
    #[Route('/catalogue', name: 'app_catalog')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator
    ): Response {
        // Récupérer les paramètres de filtrage
        $categoryId = $request->query->get('category');
        $search = $request->query->get('search');
        $sortBy = $request->query->get('sort', 'name'); // Par défaut : nom
        $order = $request->query->get('order', 'asc'); // Par défaut : croissant

        // Construire la requête
        $queryBuilder = $productRepository->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c');

        // Filtre par catégorie
        if ($categoryId) {
            $queryBuilder->andWhere('p.category = :category')
                ->setParameter('category', $categoryId);
        }

        // Filtre par recherche
        if ($search) {
            $queryBuilder->andWhere('p.name LIKE :search OR p.animeName LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Tri
        switch ($sortBy) {
            case 'price':
                $queryBuilder->orderBy('p.price', $order);
                break;
            case 'date':
                $queryBuilder->orderBy('p.createdAt', 'DESC');
                break;
            default:
                $queryBuilder->orderBy('p.name', $order);
        }

        $products = $paginator->paginate(
        $queryBuilder,
        $request->query->getInt('page', 1),
        6,
        [
            'sortFieldParameterName' => 'fake-sort', 
        ]
);

        // Récupérer toutes les catégories pour le filtre
        $categories = $categoryRepository->findAll();

        return $this->render('catalog/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $categoryId,
            'currentSearch' => $search,
            'currentSort' => $sortBy,
            'currentOrder' => $order,
        ]);
    }

    #[Route('/catalogue/categorie/{slug}', name: 'app_catalog_category')]
    public function category(
        string $slug,
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator
    ): Response {
        $category = $categoryRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie introuvable');
        }

        $queryBuilder = $productRepository->createQueryBuilder('p')
            ->where('p.category = :category')
            ->setParameter('category', $category)
            ->orderBy('p.name', 'ASC');

        $products = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            12
        );

        $categories = $categoryRepository->findAll();

        return $this->render('catalog/category.html.twig', [
            'category' => $category,
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    #[Route('/produit/{slug}', name: 'app_product_show')]
    public function show(
        string $slug,
        ProductRepository $productRepository
    ): Response {
        $product = $productRepository->findOneBy(['slug' => $slug]);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable');
        }

        // Produits similaires (même catégorie)
        $similarProducts = $productRepository->createQueryBuilder('p')
            ->where('p.category = :category')
            ->andWhere('p.id != :productId')
            ->setParameter('category', $product->getCategory())
            ->setParameter('productId', $product->getId())
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();

        return $this->render('catalog/show.html.twig', [
            'product' => $product,
            'similarProducts' => $similarProducts,
        ]);
    }
}