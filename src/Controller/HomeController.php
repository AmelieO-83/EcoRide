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
        return $this->render('mention.html.twig', [
            'title' => 'Mentions légales',
        ]);
    }
    #[Route('/contact', name: 'contact', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('contact.html.twig');
    }
    #[Route('/connexion', name: 'connexion', methods: ['GET', 'POST'])]
    public function login(): Response
    {
        // on ne gère pas encore la soumission, on affiche juste le formulaire
        return $this->render('connexion.html.twig');
    }
}
