<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Affiche et traite le formulaire de connexion (GET affiche, POST intercepté par Symfony).
     */
    #[Route('/connexion', name: 'connexion', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authUtils): Response
    {
        // Si déjà authentifié, rediriger vers "mon_compte"
        if ($this->getUser()) {
            return $this->redirectToRoute('mon_compte');
        }

        // Récupère l’erreur de connexion s’il y en a
        $error = $authUtils->getLastAuthenticationError();
        // Dernier nom d’utilisateur entré (email)
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('connexion.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * Déconnexion gérée via le firewall, ne doit pas exécuter de code.
     */
    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method is intercepted by the firewall and should not be called directly.');
    }
}
