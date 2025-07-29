<?php
// src/Controller/EmployeController.php
namespace App\Controller;

use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmployeController extends AbstractController
{
    public function __construct(
        private AvisRepository         $avisRepo
    ) {}
    #[Route('/employe', name: 'employe')]
    public function reviews(): Response
    {
        // Récupère uniquement les avis en attente
        $avis = $this->avisRepo->findEnAttente();

        return $this->render('utilisateurs/employe.html.twig', [
            'avis' => $avis,
        ]);
    }
}
