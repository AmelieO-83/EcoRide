<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function home() : Response
    {
    return $this->render('home/index.html.twig', [
            'title' => 'Bienvenue sur EcoRide',
        ]);
    }
}
