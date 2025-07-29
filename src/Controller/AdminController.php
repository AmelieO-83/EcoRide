<?php
// src/Controller/AdminController.php
namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin', methods: ['GET'])]
    public function index(): Response
    {
        // tu peux injecter ici d’autres services si nécessaire
        return $this->render('utilisateurs/admin.html.twig');
    }

    #[Route('/admin/utilisateurs', name: 'admin_utilisateurs', methods: ['GET'])]
    public function utilisateurs(UtilisateurRepository $repo): Response
    {
        $users = $repo->findAll();
        return $this->render('utilisateurs/admin_utilisateurs.html.twig', [
            'users' => $users,
        ]);
    }
}
