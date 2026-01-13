<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StaticPagesController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_legal')]
    public function legal(): Response
    {
        return $this->render('static_pages/legal.html.twig');
    }

    #[Route('/conditions-generales-vente', name: 'app_cgv')]
    public function cgv(): Response
    {
        return $this->render('static_pages/cgv.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('static_pages/contact.html.twig');
    }
}