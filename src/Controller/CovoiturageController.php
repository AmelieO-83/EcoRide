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
        // 1) On lit les critères (par défaut chaînes vides pour faciliter les tests)
        $depart   = $request->query->get('depart', '');
        $arrivee  = $request->query->get('arrivee', '');
        $dateStr  = $request->query->get('date', '');
        $dateObj  = $dateStr ? new \DateTimeImmutable($dateStr) : null;
        $ecologique = $request->query->getBoolean('ecologique');
        $fumeur   = $request->query->getBoolean('fumeur');
        $animaux  = $request->query->getBoolean('animaux');

        // 2) A-t-on lancé une vraie recherche ?
        $searchPerformed = (bool) ($depart || $arrivee || $dateStr || $ecologique || $fumeur || $animaux);

        // nouveau flag : on montre les filtres si on a un paramètre filtre dans l’URL
        $showFilters = 
        $request->query->has('ecologique') ||
        $request->query->has('fumeur') ||
        $request->query->has('animaux');

        // 3) Si oui on filtre, sinon on prend tout
        $covoiturages = $searchPerformed
            ? $repo->findByFilters($depart, $arrivee, $dateObj, $ecologique ? 'electrique' : '', $fumeur, $animaux)
            : $repo->findAll();

        // 4) On rend la vue
        return $this->render('covoiturages/index.html.twig', [
            'covoiturages'    => $covoiturages,
            'depart'          => $depart,
            'arrivee'         => $arrivee,
            'dateStr'         => $dateStr,
            'ecologique'      => $ecologique,
            'fumeur'          => $fumeur,
            'animaux'         => $animaux,
            'searchPerformed' => $searchPerformed,
            'showFilters'     => $showFilters,
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
