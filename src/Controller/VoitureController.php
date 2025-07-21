<?php
// src/Controller/VoitureController.php
namespace App\Controller;

use App\Entity\Voiture;
use App\Form\VoitureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VoitureController extends AbstractController
{
    #[Route('/voitures/ajouter', name: 'voiture_ajouter')]
    public function ajouter(Request $request, EntityManagerInterface $em): Response
    {
        $voiture = new Voiture();
        // Optionnel : lier la voiture à l'utilisateur connecté si nécessaire
        // $voiture->setProprietaire($this->getUser());

        $form = $this->createForm(VoitureType::class, $voiture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($voiture);
            $em->flush();

            $this->addFlash('success', 'Voiture ajoutée avec succès.');
            return $this->redirectToRoute('mon_compte');
        }

        return $this->render('voitures/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
