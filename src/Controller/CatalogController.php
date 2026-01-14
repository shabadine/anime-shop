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
        // Récupération des filtres depuis l'URL
        $categoryId = $request->query->get('category');
        $search = $request->query->get('search');
        $sortBy = $request->query->get('sort', 'name');
        $order = $request->query->get('order', 'asc');

        // Appel de la requête filtrée du Repository
        $queryBuilder = $productRepository->findFilteredProductsQuery(
            $categoryId, 
            $search, 
            $sortBy, 
            $order
        );

        $products = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('catalog/index.html.twig', [
            'products' => $products,
            'categories' => $categoryRepository->findAll(),
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

        // Utilisation de la méthode spécifique du Repo pour lister une catégorie
        $queryBuilder = $productRepository->findByCategoryQuery($category);

        $products = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('catalog/category.html.twig', [
            'category' => $category,
            'products' => $products,
            'categories' => $categoryRepository->findAll(),
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

        return $this->render('catalog/show.html.twig', [
            'product' => $product,
            'similarProducts' => $productRepository->findSimilarProducts($product),
        ]);
    }
}