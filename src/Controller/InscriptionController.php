<?php
// src/Controller/InscriptionController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'inscription', methods: ['GET'])]
    public function register(): Response
    {
        // Aucun traitement serveur : tout se fait en JS via l'API
        return $this->render('inscription.html.twig');
    }
}
