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

    #[Route('/admin/utilisateurs/{id}', name: 'admin_utilisateurs_delete', methods: ['POST'])]
    public function deleteUtilisateur(Utilisateur $user, Request $request): Response
    {
        // Vérifie le CSRF token
        if (!$this->isCsrfTokenValid('delete-user-' . $user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token invalide');
        }

        $this->manager->remove($user);
        $this->manager->flush();
        $this->addFlash('success', 'Utilisateur supprimé.');

        return $this->redirectToRoute('admin_utilisateurs');
    }
}
