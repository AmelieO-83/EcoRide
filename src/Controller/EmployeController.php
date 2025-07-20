<?php
// src/Controller/EmployeController.php
namespace App\Controller;

use App\Enum\AvisStatut;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EmployeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private AvisRepository         $avisRepo
    ) {}
    #[Route('/employe', name: 'employe')]
    public function reviews(): Response
    {
        // Récupère uniquement les avis en attente
        $avis = $this->avisRepo->findBy([
            'statut' => AvisStatut::EnAttente,
        ]);

        return $this->render('utilisateurs/employe.html.twig', [
            'avis' => $avis,    // <— on passe ici “avis”
        ]);
    }
}
