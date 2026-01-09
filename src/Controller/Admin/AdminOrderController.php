<?php

namespace App\Controller\Admin;   


use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/commande')]
#[IsGranted('ROLE_ADMIN')]
class AdminOrderController extends AbstractController
{
    #[Route('/', name: 'admin_order_index')]
    public function index(Request $request, OrderRepository $orderRepository): Response
    {
        $status = $request->query->get('status');
        
        if ($status) {
            $orders = $orderRepository->findBy(['status' => $status], ['createdAt' => 'DESC']);
        } else {
            $orders = $orderRepository->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
            'currentStatus' => $status,
        ]);
    }

    #[Route('/{id}', name: 'admin_order_show')]
    public function show(Order $order): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/statut', name: 'admin_order_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, Order $order, EntityManagerInterface $em): Response
    {
        $newStatus = $request->request->get('status');
        
        $allowedStatuses = ['en_attente', 'validee', 'expediee', 'livree', 'annulee'];
        
        if (in_array($newStatus, $allowedStatuses)) {
            $order->setStatus($newStatus);
            $em->flush();
            
            $this->addFlash('success', 'Statut mis à jour');
        }

        return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
    }
}