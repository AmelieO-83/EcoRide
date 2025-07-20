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
        $depart  = $request->query->get('depart');
        $arrivee = $request->query->get('arrivee');
        $date    = $request->query->get('date');
        $dateObj = $date ? new \DateTimeImmutable($date) : null;

        $searchPerformed = !empty($depart) || !empty($arrivee) || !empty($date);

        if ($searchPerformed) {
            $covoiturages = $repo->findByFilters($depart, $arrivee, $dateObj);
        } else {
            $covoiturages = $repo->findAll();
        }

        return $this->render('covoiturages/index.html.twig', [
            'covoiturages' => $covoiturages,
            'criteres'     => [
                'depart'  => $depart,
                'arrivee' => $arrivee,
                'date'    => $date,
            ],
            'searchPerformed' => $searchPerformed,
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
