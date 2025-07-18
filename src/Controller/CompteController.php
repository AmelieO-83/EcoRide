<?php
// src/Controller/CompteController.php
namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompteController extends AbstractController
{
    #[Route('/mon-compte', name: 'mon_compte', methods: ['GET'])]
    public function monCompte(): Response
    {
        // On se contente de rendre le template : le JS fera le reste.
        return $this->render('utilisateurs/moncompte.html.twig');
    }
}
