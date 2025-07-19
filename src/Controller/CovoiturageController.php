<?php
// src/Controller/CovoiturageController.php
namespace App\Controller;

use App\Repository\CovoiturageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CovoiturageController extends AbstractController
{
    #[Route('/covoiturages', name: 'covoiturages_list', methods: ['GET'])]
    public function list(CovoiturageRepository $repo): Response
    {
        $covoiturages = $repo->findAll();
        return $this->render('covoiturages/index.html.twig', [
            'covoiturages' => $covoiturages,
        ]);
    }

    #[Route('/covoiturages/{id}', name: 'trajet_show', methods: ['GET'])]
    public function show(int $id, CovoiturageRepository $repo): Response
    {
        $trajet = $repo->find($id);
        if (!$trajet) {
            throw $this->createNotFoundException("Trajet #{$id} introuvable");
        }

        return $this->render('covoiturages/show.html.twig', [
            'trajet' => $trajet,
        ]);
    }
}
