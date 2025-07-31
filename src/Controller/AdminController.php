<?php
// src/Controller/AdminController.php
namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface    $manager,
        private UtilisateurRepository     $utilisateurRepo,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Affichage des stats (votre code existant)
        return $this->render('utilisateurs/admin.html.twig');
    }

    #[Route('/admin/utilisateurs', name: 'admin_utilisateurs', methods: ['GET'])]
    public function listUtilisateurs(): Response
    {
        $users = $this->utilisateurRepo->findAll();
        return $this->render('utilisateurs/admin_utilisateurs.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/utilisateurs/{id}/edit', name: 'admin_utilisateurs_edit', methods: ['GET','POST'])]
    public function editUser(Utilisateur $user, Request $request): Response
    {
        $form = $this->createForm(UtilisateurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1) Si on a saisi un nouveau mot de passe, on l’encode
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $hash = $this->passwordHasher->hashPassword($user, $plain);
                $user->setPassword($hash);
            }

            // 2) flush() sauvera aussi le nouveau tableau de roles
            $this->manager->flush();

            $this->addFlash('success', 'Utilisateur mis à jour.');
            return $this->redirectToRoute('admin_utilisateurs');
        }

        return $this->render('utilisateurs/admin_utilisateurs_edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/admin/utilisateurs/{utilisateur}/delete', name: 'admin_utilisateurs_delete', methods: ['POST'])]
    public function deleteUtilisateur(Utilisateur $utilisateur, Request $request): Response
    {
        // 1- Vérifier le CSRF token
        if (
            ! $this->isCsrfTokenValid(
                'delete-user-' . $utilisateur->getId(),
                $request->request->get('_token')
            )
        ) {
            throw $this->createAccessDeniedException('Token invalide');
        }

        // 2- Supprimer et flusher
        $this->manager->remove($utilisateur);
        $this->manager->flush();

        // 3- Message flash et redirection
        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('admin_utilisateurs');
    }
    #[Route('/admin/utilisateurs/new', name: 'admin_utilisateurs_new', methods: ['GET','POST'])]
    public function newUser(Request $request): Response
    {
        $user = new Utilisateur();
        // on génère un mot de passe vide par défaut pour forcer l’Admin à en saisir un
        $form = $this->createForm(UtilisateurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1) on encode le mot de passe
            $plain = $form->get('plainPassword')->getData();
            if (!$plain) {
                $this->addFlash('danger', 'Vous devez renseigner un mot de passe.');
                // on retombe sur le form
            } else {
                $hash = $this->passwordHasher->hashPassword($user, $plain);
                $user->setPassword($hash);

                // 2) persist + flush
                $this->manager->persist($user);
                $this->manager->flush();

                $this->addFlash('success', 'Nouvel utilisateur créé.');
                return $this->redirectToRoute('admin_utilisateurs');
            }
        }

        return $this->render('utilisateurs/admin_utilisateurs_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
