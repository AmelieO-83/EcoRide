<?php
// src/Controller/CovoiturageController.php
namespace App\Controller;

use App\Entity\Covoiturage;
use App\Enum\NotificationType;
use App\Repository\CovoiturageRepository;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\NotificationService;

#[Route('/api/covoiturages', name: 'api_covoiturages_')]
class CovoiturageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface  $manager,
        private SerializerInterface     $serializer,
        private ValidatorInterface      $validator,
        private CovoiturageRepository   $covoiturageRepository,
        private VoitureRepository       $voitureRepository,
        private NotificationService     $notificationService
    ){}
    /**
     * Cette même méthode répondra :
     *  - en JSON sur /api/covoiturages         (route "api_covoiturages_list")
     *  - en HTML Twig sur /covoiturages         (route "app_covoiturages_list")
     */
    #[Route('/api/covoiturages', name:'api_covoiturages_list', methods:['GET'])]
    #[Route('/covoiturages', name:'app_covoiturages_list', methods:['GET'])]
    public function list(Request $request): Response|JsonResponse
    {
        // 1) Lecture des filtres
        $depart   = $request->query->get('depart');
        $arrivee  = $request->query->get('arrivee');
        $date     = $request->query->get('date')
                  ? new \DateTimeImmutable($request->query->get('date'))
                  : null;

        // 2) Recherche en BDD
        $voyages = $this->covoiturageRepository->findByFilters($depart, $arrivee, $date);

        // 3) Si on est sur la route API, on renvoie du JSON
        if ($request->attributes->get('_route') === 'api_covoiturages_list') {
            $json = $this->serializer->serialize(
                $voyages,
                'json',
                ['groups'=>['covoiturage:read'], DateTimeNormalizer::FORMAT_KEY => 'Y-m-d']
            );

            return new JsonResponse($json, Response::HTTP_OK, [], true);
        }

        // 4) Sinon, on rend le Twig "list.html.twig"
        return $this->render('covoiturage/list.html.twig', [
            'covoiturages' => $voyages,
            'filters'      => ['depart'=>$depart,'arrivee'=>$arrivee,'date'=>$date],
        ]);
    }

    /**
     * POST /api/covoiturages
     * Crée un nouveau trajet pour l’utilisateur courant.
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] $utilisateur): JsonResponse
    {
        // 1. On extrait d’abord le JSON en tableau pour récupérer l’ID de la voiture
        $data = json_decode($request->getContent(), true);

        // 2. On instancie le Covoiturage sans encore toucher à la voiture
        $covoiturage = $this->serializer->deserialize(
            $request->getContent(),
            Covoiturage::class,
            'json',
            ['groups'=>['covoiturage:write'], DateTimeNormalizer::FORMAT_KEY => 'd/m/Y']
        );

        // 3. On va chercher la Voiture gérée par Doctrine
        $voiture = $this->voitureRepository->find($data['voiture'] ?? null);
        if (!$voiture) {
            return $this->json(['error' => 'Voiture introuvable'], Response::HTTP_NOT_FOUND);
        }
        $covoiturage->setVoiture($voiture);

        dump('CHAUFFEUR AVANT ASSOC :', $utilisateur->getId(), $utilisateur->getEmail());

        // 4. On associe le chauffeur
        $covoiturage->setChauffeur($utilisateur);

        dump('CHAUFFEUR APRÈS ASSOC :', $covoiturage->getChauffeur()?->getId());

        // 5. Validation
        $errors = $this->validator->validate($covoiturage);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 6. Persistence
        $this->manager->persist($covoiturage);
        $this->manager->flush();

        // 7. Retour
        $json = $this->serializer->serialize($covoiturage, 'json', ['groups'=>['covoiturage:read']]);
        return new JsonResponse(
            $json,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('api_covoiturages_show', ['id' => $covoiturage->getId()])],
            true
        );
    }

    /**
     * GET /api/covoiturages/{id}
     * Affiche un trajet.
     */
    #[Route('/{id}', name:'show', methods:['GET'])]
    public function show(int $id): JsonResponse
    {
        $covoiturage = $this->covoiturageRepository->find($id);
        if (!$covoiturage) {
            return $this->json(['error'=>'Introuvable'], Response::HTTP_NOT_FOUND);
        }
        $json = $this->serializer->serialize(
            $covoiturage,
            'json',
            ['groups'=>['covoiturage:read']]
        );
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * PUT /api/covoiturages/{id}
     * Met à jour un trajet (seul le chauffeur peut).
     */
    #[Route('/{id}', name:'update', methods:['PUT'])]
    public function update(int $id, Request $request, #[CurrentUser] $utilisateur): JsonResponse
    {
        $covoiturage = $this->covoiturageRepository->find($id);
        if (!$covoiturage) {
            return $this->json(['error'=>'Introuvable'], Response::HTTP_NOT_FOUND);
        }
        if ($covoiturage->getChauffeur() !== $utilisateur) {
            return $this->json(['error'=>'Interdit'], Response::HTTP_FORBIDDEN);
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Covoiturage::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $covoiturage]
        );

        $errors = $this->validator->validate($covoiturage);
        if (count($errors)>0) {
            return $this->json(['errors'=>(string)$errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * DELETE /api/covoiturages/{id}
     * Supprime un trajet (proprio ou admin).
     */
    #[Route('/{id}', name:'delete', methods:['DELETE'])]
    public function delete(int $id, #[CurrentUser] $utilisateur): JsonResponse
    {
        $covoiturage = $this->covoiturageRepository->find($id);
        if (!$covoiturage) {
            return $this->json(['error'=>'Introuvable'], Response::HTTP_NOT_FOUND);
        }
        $isAdmin = in_array('ROLE_ADMIN', $utilisateur->getRoles(), true);
        if (!$isAdmin && $covoiturage->getChauffeur() !== $utilisateur) {
            return $this->json(['error'=>'Interdit'], Response::HTTP_FORBIDDEN);
        }

        // 1) Récupérer la liste des passagers avant suppression
        $passagers = $covoiturage->getPassagers()->toArray();

        $this->manager->remove($covoiturage);
        $this->manager->flush();

        // Notifier chaque passager de l’annulation par le conducteur
        foreach ($passagers as $passager) {
            $this->notificationService->trigger(
                NotificationType::AnnulationConducteur,
                $passager,
                [
                    'conducteur' => $utilisateur,
                    'trajet'     => sprintf('%s → %s', $covoiturage->getVilleDepart(), $covoiturage->getVilleArrivee()),
                ]
            );
        }
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
