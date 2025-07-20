<?php
// src/Controller/VoitureController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VoitureController extends AbstractController
{
    #[Route('/voitures/ajouter', name: 'voiture_ajouter', methods: ['GET'])]
    public function add(): Response
    {
        // on renvoie juste le formulaire
        return $this->render('voiture/ajouter.html.twig');
    }
}
