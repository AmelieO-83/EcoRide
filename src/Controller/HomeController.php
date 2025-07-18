<?php

namespace App\Controller;

use App\Repository\CovoiturageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    #[Route('/', name: 'accueil', methods: ['GET'])]
    public function home(CovoiturageRepository $covoituragerepository) : Response
    {
        $covoiturages = $covoituragerepository->findAll(); // récupère tous les trajets
        return $this->render('accueil.html.twig', [
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
    #[Route('/connexion', name: 'connexion', methods: ['GET','POST'])]
    public function login(AuthenticationUtils $authUtils): Response
    {
        // Récupère l’erreur de connexion (si noms d’utilisateur / mot de passe invalides)
        $error = $authUtils->getLastAuthenticationError();
        // Préremplit le dernier username essayé
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('connexion.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
    #[Route('/inscription', name: 'inscription', methods: ['GET', 'POST'])]
    public function register(): Response
    {
        return $this->render('inscription.html.twig');
    }
}
