<?php
// src/Controller/EmployeController.php
namespace App\Controller;

use App\Entity\Avis;
use App\Enum\AvisStatut;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{RedirectResponse, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe', name: 'employe_')]
class EmployeController extends AbstractController
{
    public function __construct(private AvisRepository $avisRepo) {}

    /**
     * GET /employe/avis
     */
    #[Route('/avis', name: 'avis')]
    public function index(): Response
    {
        $data = [
            'en_attente' => $this->avisRepo->findEnAttente(),
            'valides'    => $this->avisRepo->findValides(),
            'rejetes'    => $this->avisRepo->findRejetes(),
        ];

        return $this->render('utilisateurs/employe.html.twig', [
            'data' => $data,
        ]);
    }

    #[Route('/avis/{id}', name: 'avis_detail', methods: ['GET'])]
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

    /**
     * POST /employe/avis/{id}/valider
     */
    #[Route('/avis/{id}/valider', name: 'avis_valider', methods: ['POST'])]
    public function valider(int $id, EntityManagerInterface $em): RedirectResponse
    {
        /** @var Avis|null $avis */
        $avis = $this->avisRepo->find($id);
        if (!$avis) {
            throw $this->createNotFoundException("Avis #$id introuvable");
        }
        $avis->setStatut(AvisStatut::Valide);
        $em->flush();

        $this->addFlash('success', 'Avis validé.');
        return $this->redirectToRoute('employe_avis');
    }

    /**
     * POST /employe/avis/{id}/rejeter
     */
    #[Route('/avis/{id}/rejeter', name: 'avis_rejeter', methods: ['POST'])]
    public function rejeter(int $id, EntityManagerInterface $em): RedirectResponse
    {
        /** @var Avis|null $avis */
        $avis = $this->avisRepo->find($id);
        if (!$avis) {
            throw $this->createNotFoundException("Avis #$id introuvable");
        }
        $avis->setStatut(AvisStatut::Rejete);
        $em->flush();

        $this->addFlash('warning', 'Avis rejeté.');
        return $this->redirectToRoute('employe_avis');
    }
}
