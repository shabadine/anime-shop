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
       $id = $request->query->get('id');
       $id = $request->request->get('id');
       $featuredProducts = $productRepository->findBy(['featured' => true], null, 8);
       
       $bestSellers = $productRepository->findBy(['featured' => true], ['createdAt' => 'DESC'], 4);
        
        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'bestSellers' => $bestSellers,
        ]);
    }
}