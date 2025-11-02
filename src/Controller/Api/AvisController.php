<?php
// src/Controller/Api/AvisController.php
namespace App\Controller\Api;

use App\Entity\Avis;
use App\Entity\Utilisateur;
use App\Enum\AvisStatut;
use App\Enum\NotificationType;
use App\Repository\AvisRepository;
use App\Repository\CovoiturageRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\ParticipationRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/api/avis', name: 'api_avis_')]
class AvisController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface  $manager,
        private SerializerInterface     $serializer,
        private AvisRepository          $avisRepository,
        private CovoiturageRepository   $covoiturageRepository,
        private UtilisateurRepository   $utilisateurRepository,
        private NotificationService     $notificationService,
        private UrlGeneratorInterface   $urlGenerator,
        private ParticipationRepository $participationRepository
    ){}

    /**
     * POST /api/avis
     * Déposer un nouvel avis (passager → chauffeur).
     */
    #[Route('', name:'create', methods:['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, #[CurrentUser] Utilisateur $passager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // 0. Valider les données reçues
        $note = isset($data['note']) ? (int) $data['note'] : null;
        $commentaire = trim($data['commentaire'] ?? '');
        if ($note === null || $note < 1 || $note > 5) {
            throw new BadRequestHttpException('La note doit être un entier entre 1 et 5.');
        }
        if ($commentaire === '') {
            throw new BadRequestHttpException('Le commentaire ne peut pas être vide.');
        }

        // 1. Récupérer le covoiturage
        $covoiturage = $this->covoiturageRepository->find($data['covoiturageId'] ?? 0);
        if (!$covoiturage) {
            throw new NotFoundHttpException('Covoiturage introuvable');
        }

        // 2. Vérifier que l’utilisateur a bien participé à ce trajet
        $participation = $this->participationRepository
            ->findOneBy(['passager' => $passager, 'covoiturage' => $covoiturage]);
        if (!$participation) {
            throw new AccessDeniedHttpException(
                'Vous ne pouvez pas noter un trajet auquel vous n’avez pas participé.'
            );
        }

        // 3. Déduire le destinataire de l’avis
        $chauffeur = $covoiturage->getChauffeur();
        if (!$chauffeur) {
            throw new BadRequestHttpException('Impossible de déterminer le chauffeur pour ce covoiturage');
        }

        // 4. Récupérer l’utilisateur connecté comme auteur de l’avis
        if (!$passager) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour noter.');
        }

        // 5. Créer l’Avis
        $avis = new Avis();
        $avis
            ->setCovoiturage($covoiturage)
            ->setAuteur($passager)
            ->setDestinataire($chauffeur)
            ->setNote($note)
            ->setCommentaire($commentaire)
            ->setStatut(AvisStatut::EnAttente);

        // 6. Persister
        $this->manager->persist($avis);
        $this->manager->flush();

        // 7. Prévenir tous les employés qu’un avis est en attente de validation
        try {
            $urlValider = $this->urlGenerator->generate(
                'api_avis_validate',
                ['id' => $avis->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            foreach ($this->utilisateurRepository->findByRole('ROLE_EMPLOYE') as $employe) {
                $this->notificationService->trigger(
                    NotificationType::AvisAValider,
                    $employe,
                    ['avis'=>$avis, 'url_valider_avis'=>$urlValider]
                );
            }
        } catch (\Throwable $e) { /* log si besoin */ }

        $json = $this->serializer->serialize($avis, 'json', ['groups'=>['avis:read']]);
        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    /**
     * GET /api/avis
     * Liste ses avis reçus ou, si employé, tous les avis en attente.
     */
    #[Route('', name:'list', methods:['GET'])]
    public function list(#[CurrentUser] Utilisateur $utilisateur): JsonResponse
    {
        if (!$this->isGranted('ROLE_EMPLOYE')) {
            // usage pour passagers / chauffeurs
            $avis = $this->avisRepository->findRecusParUtilisateur($utilisateur);
            $json = $this->serializer->serialize($avis,'json',['groups'=>['avis:read']]);
            return new JsonResponse($json,200,[],true);
        }

        // pour les employés, on retourne un objet groupé
        $enAttente = $this->avisRepository->findEnAttente();
        $valides   = $this->avisRepository->findValides();
        $rejetes   = $this->avisRepository->findRejetes();

        return $this->json([
            'en_attente' => json_decode($this->serializer->serialize($enAttente, 'json', ['groups'=>['avis:read']]), true),
            'valides'    => json_decode($this->serializer->serialize($valides,    'json', ['groups'=>['avis:read']]), true),
            'rejetes'    => json_decode($this->serializer->serialize($rejetes,    'json', ['groups'=>['avis:read']]), true),
        ], Response::HTTP_OK);
    }

    /**
     * GET /api/avis/{id}
     */
    #[Route('/{id<\d+>}', name:'show', methods:['GET'])]
    public function show(int $id, #[CurrentUser] Utilisateur $utilisateur): JsonResponse
    {
        $avis = $this->avisRepository->find($id);
        if (!$avis) {
            return $this->json(['error'=>'Introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Seul le destinataire ou un employé peut y accéder
        if (!$this->isGranted('ROLE_EMPLOYE') && $avis->getDestinataire() !== $utilisateur) {
            return $this->json(['error'=>'Interdit'], Response::HTTP_FORBIDDEN);
        }

        $json = $this->serializer->serialize($avis, 'json', ['groups'=>['avis:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * PUT /api/avis/{id}/valider
     * Valider un avis (seul ROLE_EMPLOYE).
     */
    #[Route('/{id}/valider', name:'validate', methods:['PUT','POST'])]
    #[IsGranted('ROLE_EMPLOYE')]
    public function validate(int $id): JsonResponse
    {
        $avis = $this->avisRepository->find($id);
        if (!$avis) {
            return $this->json(['error'=>'Avis Introuvable'], Response::HTTP_NOT_FOUND);
        }
        switch ($avis->getStatut()) {
            case AvisStatut::Valide:
                // Déjà validé → OK idempotent
                $json = $this->serializer->serialize($avis, 'json', ['groups' => ['avis:read']]);
                return new JsonResponse($json, Response::HTTP_OK, [], true);

            case AvisStatut::Rejete:
                // Incohérent de “valider” un avis déjà rejeté
                return $this->json(['error' => 'Avis déjà rejeté'], Response::HTTP_CONFLICT);

            case AvisStatut::EnAttente:
                // Chemin nominal : on valide maintenant
                $avis->setStatut(AvisStatut::Valide);
                $this->manager->flush();
                break;
        }

        $urlAvis = $this->urlGenerator->generate(
            'api_avis_show',
            ['id' => $avis->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        // Prévenir le chauffeur qu’un **nouvel avis** lui a été laissé
        try {
            $chauffeur = $avis->getDestinataire();   // ← correct
            $this->notificationService->trigger(
                NotificationType::NouvelAvis,
                $chauffeur,
                [
                    'chauffeur' => $chauffeur,
                    'passager'  => $avis->getAuteur(),
                    'trajet'    => sprintf('%s → %s',
                        $avis->getCovoiturage()->getVilleDepart(),
                        $avis->getCovoiturage()->getVilleArrivee()
                    ),
                    'url_avis'  => $urlAvis,
                ]
            );
        } catch (\Throwable $e) { /* log si besoin */ }

        $json = $this->serializer->serialize($avis, 'json', ['groups'=>['avis:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * PUT /api/avis/{id}/rejeter
     * Rejeter un avis (ROLE_ADMIN ou ROLE_EMPLOYE).
     */
    #[Route('/{id}/rejeter', name:'reject', methods:['PUT','POST'])]
    public function reject(int $id, #[CurrentUser] Utilisateur $utilisateur): JsonResponse
    {
        $avis = $this->avisRepository->find($id);
        if (!$avis) {
            return $this->json(['error'=>'Introuvable'], Response::HTTP_NOT_FOUND);
        }

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isEmploye = $this->isGranted('ROLE_EMPLOYE');

        if (! $isAdmin && ! $isEmploye && $avis->getAuteur() !== $utilisateur) {
            return $this->json(['error'=>'Interdit'], Response::HTTP_FORBIDDEN);
        }

        $avis->setStatut(AvisStatut::Rejete);
        $this->manager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/recus', name: 'recus', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function listRecus(#[CurrentUser] Utilisateur $user): JsonResponse
    {
        $avis = $this->avisRepository->findBy([
            'destinataire' => $user,
            'statut'       => AvisStatut::Valide
        ], ['dateCreation'=>'DESC']);

        $data = array_map(fn(Avis $a) => [
            'note'         => $a->getNote(),
            'commentaire'  => $a->getCommentaire(),
            'dateCreation' => $a->getDateCreation()->format(\DateTimeInterface::ATOM),
        ], $avis);

        return $this->json($data);
    }
}
