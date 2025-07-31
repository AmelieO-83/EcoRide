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
        private AvisRepository $avisRepo
    ) {}

    #[Route('/employe', name: 'employe')]
    public function reviews(): Response
    {
        // pas besoin de passer quoi que ce soit, la liste se fait en JS
        return $this->render('utilisateurs/employe.html.twig');
    }

    #[Route('/employe/avis/{id}', name: 'employe_avis_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function avisDetail(int $id): Response
    {
        $avis = $this->avisRepo->find($id);
        if (!$avis) {
            throw $this->createNotFoundException("Avis introuvable");
        }

        return $this->render('utilisateurs/employe_avis_detail.html.twig', [
            'avis' => $avis,
        ]);
    }
}
