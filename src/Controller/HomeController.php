<?php

namespace App\Controller;

use App\Repository\CovoiturageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(CovoiturageRepository $covoituragerepository) : Response
    {
        $covoiturages = $covoituragerepository->findAll(); // récupère tous les trajets
        return $this->render('home/index.html.twig', [
            'title' => 'Bienvenue sur EcoRide',
            'covoiturages' => $covoiturages,
        ]);
    }
    #[Route('/mentions-legales', name: 'mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('mentions.html.twig', [
            'title' => 'Mentions légales',
        ]);
    }

}
