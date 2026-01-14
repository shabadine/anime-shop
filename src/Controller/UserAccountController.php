<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Form\UserProfileType;
use App\Entity\User;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/compte')]
#[IsGranted('ROLE_USER')]
class UserAccountController extends AbstractController
{
    #[Route('/', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('user_account/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/profil', name: 'app_account_profile')]
    public function profile(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si un nouveau mot de passe est fourni
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès !');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('user_account/profile.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/commandes', name: 'app_account_orders')]
    public function orders(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findBy(
            ['user' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('user_account/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/commande/{id}', name: 'app_account_order_show')]
    public function orderShow(int $id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande introuvable');
        }

        return $this->render('user_account/order_show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/adresses', name: 'app_account_addresses')]
    public function addresses(): Response
    {   /** @var User $user */
        $user = $this->getUser();

        return $this->render('user_account/addresses.html.twig', [
            'addresses' => $user->getAddresses(),
        ]);
    }

    #[Route('/adresse/nouvelle', name: 'app_account_address_new')]
    public function addressNew(Request $request, EntityManagerInterface $em): Response
    {
        $address = new Address();
        $address->setUser($this->getUser());

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($address);
            $em->flush();

            $this->addFlash('success', 'Adresse ajoutée avec succès !');
            return $this->redirectToRoute('app_account_addresses');
        }

        return $this->render('user_account/address_form.html.twig', [
            'form' => $form,
            'isEdit' => false,
        ]);
    }

    #[Route('/adresse/{id}/modifier', name: 'app_account_address_edit')]
    public function addressEdit(
        Address $address,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Adresse modifiée avec succès !');
            return $this->redirectToRoute('app_account_addresses');
        }

        return $this->render('user_account/address_form.html.twig', [
            'form' => $form,
            'isEdit' => true,
        ]);
    }

    #[Route('/adresse/{id}/supprimer', name: 'app_account_address_delete', methods: ['POST'])]
    public function addressDelete(
        Address $address,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$address->getId(), $request->request->get('_token'))) {
            $em->remove($address);
            $em->flush();

            $this->addFlash('success', 'Adresse supprimée');
        }

        return $this->redirectToRoute('app_account_addresses');
    }
}