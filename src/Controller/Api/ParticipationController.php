<?php
// src/Controller/Api/ParticipationController.php
namespace App\Controller\Api;

use App\Entity\Participation;
use App\Entity\Utilisateur;
use App\Enum\NotificationType;
use App\Repository\ParticipationRepository;
use App\Repository\CovoiturageRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class ParticipationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface      $manager,
        private SerializerInterface         $serializer,
        private ParticipationRepository     $participationRepository,
        private CovoiturageRepository       $covoiturageRepository,
        private NotificationService         $notificationService,
        private \App\Service\Frais          $fraisService,
        private \App\Service\StatsService   $statsService
    ){}

    /**
     * POST /api/covoiturages/{id}/participer
     * L’utilisateur courant rejoint le covoiturage.
     */
    #[Route('/covoiturages/{id}/participer', name: 'participation_participer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function participer(int $id, #[CurrentUser] Utilisateur $utilisateur): JsonResponse
    {
        $covoiturage = $this->covoiturageRepository->find($id);
        if (!$covoiturage) {
            return $this->json(['error'=>'Trajet non trouvé'], Response::HTTP_NOT_FOUND);
        }
        
        // Empêcher le chauffeur de participer à son propre trajet
        if ($covoiturage->getChauffeur() === $utilisateur) {
            return $this->json(
                ['error' => 'Vous ne pouvez pas participer à votre propre trajet.'],
                Response::HTTP_FORBIDDEN
            );
        }

        // Vérifie participation existante
        if ($this->participationRepository->findOneBy(['passager' => $utilisateur, 'covoiturage' => $covoiturage])) {
            return $this->json(['error' => 'Vous participez déjà à ce trajet'], Response::HTTP_CONFLICT);
        }

        // Vérifie places et crédit
        $prix = $covoiturage->getPrix();
        if ($covoiturage->getPlacesDisponibles() < 1) {
            return $this->json(['error'=>'Plus de places disponibles'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($utilisateur->getCredit() < $prix) {
            return $this->json(['error'=>'Crédits insuffisants'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        // Débit passager et décrémente place
        $utilisateur->setCredit($utilisateur->getCredit() - $prix);
        $covoiturage->setPlacesDisponibles($covoiturage->getPlacesDisponibles() - 1);

        // Crée et persiste la participation
        $participation = new Participation();
        $participation
            ->setPassager($utilisateur)
            ->setCovoiturage($covoiturage)
            ->setConfirme(false);

        $this->manager->persist($participation);
        $this->manager->flush();

        // Notifie le chauffeur d'un nouveau passager
        try {
            $this->notificationService->trigger(
                NotificationType::NouveauPassager,
                $covoiturage->getChauffeur(),
                [
                    'passager' => $utilisateur,
                    'trajet'   => sprintf('%s → %s', $covoiturage->getVilleDepart(), $covoiturage->getVilleArrivee()),
                ]
            );
        } catch (\Throwable $e) {
            // on log l'erreur mais on poursuit
            // logger()->error('Notification failed: '.$e->getMessage());
        }

        return $this->json(['participationId' => $participation->getId(), 'nouveauCredit'   => $utilisateur->getCredit(),], Response::HTTP_CREATED);
    }

    /**
     * DELETE /api/participations/{id}
     * L’utilisateur courant annule sa participation.
     */
    #[Route('/participations/{id}', name: 'participation_annuler', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function annuler(int $id, #[CurrentUser] Utilisateur $utilisateur): JsonResponse
    {
        $participation = $this->participationRepository->find($id);
        if (!$participation || $participation->getPassager() !== $utilisateur) {
            return $this->json(['error'=>'Participation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $covoiturage = $participation->getCovoiturage();
        // Remboursement et libération de place
        $utilisateur->setCredit($utilisateur->getCredit() + $covoiturage->getPrix());
        $covoiturage->setPlacesDisponibles($covoiturage->getPlacesDisponibles() + 1);

        $this->manager->remove($participation);
        $this->manager->flush();

        // Notifie le chauffeur de l'annulation
        try {
            $this->notificationService->trigger(
                NotificationType::AnnulationPassager,
                $covoiturage->getChauffeur(),
                [
                    'passager' => $utilisateur,
                    'trajet'   => sprintf('%s → %s', $covoiturage->getVilleDepart(), $covoiturage->getVilleArrivee()),
                ]
            );
        } catch (\Throwable $e) {
            // TODO: logger si tu veux
        }

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    /**
     * POST /api/participations/{id}/confirmer
     * Le passager confirme son trajet et crédite immédiatement le chauffeur.
     */
    #[Route('/participations/{id}/confirmer', name: 'participation_confirmer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function confirmer(int $id, #[CurrentUser] Utilisateur $utilisateur): JsonResponse
    {
        // 1) on récupère la participation
        $participation = $this->participationRepository->find($id);
        if (!$participation || $participation->getPassager() !== $utilisateur) {
            return $this->json(['error' => 'Participation non trouvée'], Response::HTTP_NOT_FOUND);
        }
        if ($participation->isConfirme()) {
            return $this->json(['message' => 'Participation déjà confirmée'], Response::HTTP_BAD_REQUEST);
        }

        // 2) Calculer le montant net à créditer
        $prix = $participation->getCovoiturage()->getPrix();
        $fraisPlateforme = $this->fraisService->getPlateforme();
        $montantNet = max(0, $prix - $fraisPlateforme);

        // 3) Créditer le chauffeur
        $chauffeur = $participation->getCovoiturage()->getChauffeur();
        $chauffeur->setCredit($chauffeur->getCredit() + $montantNet);

        // 4) Marquer la participation confirmée
        $participation->setConfirme(true);

        $this->manager->flush();
        $this->statsService->inc('rides_confirmed', 1);
        $this->statsService->inc('credits_earned', (int) $montantNet);

        // 1) Générer l’URL absolue vers la création d’un avis
        $urlAvis = $this->generateUrl(
            'api_avis_create',      // nom de ta route POST /api/avis
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // 4) Envoyer la notification “avis_a_donner” au passager
        try {
            $this->notificationService->trigger(
                NotificationType::AvisADonner,
                $utilisateur,
                [
                    'chauffeur' => $chauffeur,
                    'trajet'    => sprintf('%s → %s',
                        $participation->getCovoiturage()->getVilleDepart(),
                        $participation->getCovoiturage()->getVilleArrivee()
                    ),
                    'url_avis'  => $urlAvis,
                ]
            );
        } catch (\Throwable $e) {
            // logger()->error('Notification failed: '.$e->getMessage());
        }

        return $this->json([
            'message'          => 'Participation confirmée et chauffeur crédité',
            'montant_net'      => $montantNet,
            'credit_chauffeur' => $chauffeur->getCredit(),
        ], Response::HTTP_OK);
    }

    /**
     * GET /api/participations
     * Liste toutes les participations de l’utilisateur courant.
     */
    #[Route('/participations', name: 'participation_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(#[CurrentUser] Utilisateur $utilisateur): JsonResponse
    {
        $participations = $this->participationRepository->findByUser($utilisateur);

        $result = array_map(function(Participation $p) {
            $cov = $p->getCovoiturage();
            return [
                'id' => $p->getId(),
                'covoiturage' => [
                    'id'           => $cov->getId(),
                    'villeDepart'  => $cov->getVilleDepart(),
                    'villeArrivee' => $cov->getVilleArrivee(),
                    'date'         => $cov->getDate()->format('Y-m-d'),
                ],
                'confirme' => $p->isConfirme(),
            ];
        }, $participations);

        return $this->json($result, Response::HTTP_OK);
    }
}
