<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        UserRepository $userRepository
    ): Response {
        // Statistiques
        $totalOrders = $orderRepository->count([]);
        $totalRevenue = $orderRepository->getTotalRevenue();
        $totalProducts = $productRepository->count([]);
        $totalUsers = $userRepository->count([]);

        // Dernières commandes
        $recentOrders = $orderRepository->findBy([], ['createdAt' => 'DESC'], 10);

        // Produits les plus vendus
        $topProducts = $productRepository->findTopSelling(5);

        // Commandes par statut
        $ordersByStatus = [
            'en_attente' => $orderRepository->count(['status' => 'en_attente']),
            'validee' => $orderRepository->count(['status' => 'validee']),
            'expediee' => $orderRepository->count(['status' => 'expediee']),
            'livree' => $orderRepository->count(['status' => 'livree']),
        ];

        return $this->render('admin/dashboard/index.html.twig', [
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'totalProducts' => $totalProducts,
            'totalUsers' => $totalUsers,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'ordersByStatus' => $ordersByStatus,
        ]);
    }
}