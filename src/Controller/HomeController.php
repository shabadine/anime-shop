<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $featuredProducts = $productRepository->findBy(['featured' => true], null, 8);
        
        $bestSellers = $productRepository->createQueryBuilder('p')
            ->where('p.pricePromotion IS NOT NULL')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
        
        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'bestSellers' => $bestSellers,
        ]);
    }
}