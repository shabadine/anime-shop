<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        // Récupérer les produits en vedette
        $featuredProducts = $productRepository->findBy(['featured' => true], null, 8);
        
        // Récupérer les best-sellers 
        $bestSellers = $productRepository->findBy(['featured' => true], ['createdAt' => 'DESC'], 4);
        
        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'bestSellers' => $bestSellers,
        ]);
    }
}