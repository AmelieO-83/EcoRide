<?php
// src/Controller/CovoiturageController.php
namespace App\Controller;

use App\Repository\CovoiturageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CovoiturageController extends AbstractController
{
    #[Route('/covoiturages', name: 'covoiturages_list', methods: ['GET'])]
    public function list(Request $request, CovoiturageRepository $repo): Response
    {
        // 1) On lit la query string
        $depart   = $request->query->get('depart');
        $arrivee  = $request->query->get('arrivee');
        $dateStr  = $request->query->get('date');
        $date     = $dateStr ? new \DateTimeImmutable($dateStr) : null;
        $energie  = $request->query->get('energie');
        $fumeur  = $request->query->has('fumeur')  ? $request->query->get('fumeur') === '1'  : null;
        $animaux = $request->query->has('animaux') ? $request->query->get('animaux')==='1' : null;

        // 2) On appelle le repository
        $results = $repo->findByFilters(
            $depart,
            $arrivee,
            $date,
            $energie,
            $fumeur,
            $animaux
        );

        // 3) On rend la vue en passant tout
        return $this->render('covoiturages/index.html.twig', [
            'covoiturages'    => $results,
            'searchPerformed' => $request->query->count() > 0,
            'depart'          => $depart,
            'arrivee'         => $arrivee,
            'dateStr'         => $dateStr,
            'energie'         => $energie,
            'fumeur'          => $fumeur,
            'animaux'         => $animaux,
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
    
    #[Route('/proposer-trajet', name: 'proposer_trajet', methods: ['GET'])]
    public function proposer(): Response
    {
        return $this->render('covoiturages/proposer.html.twig');
    }
    
    #[Route('/mes-covoiturages', name: 'espace_covoiturage')]
    public function index(): Response
    {
        return $this->render('covoiturages/espace.html.twig');
    }
}
