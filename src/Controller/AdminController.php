<?php
// src/Controller/AdminController.php
namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Service\Frais;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Document\Statistique;

class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface        $manager,
        private UtilisateurRepository         $utilisateurRepo,
        private UserPasswordHasherInterface   $passwordHasher,
        private DocumentManager               $dm,      // 👈 Mongo
        private Frais                         $frais,      // 👈 frais plateforme
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // ---------- 1) Récupération des stats MongoDB (collection "statistiques") ----------
        $coll = $this->dm->getDocumentCollection(Statistique::class);

        $buildSeries = function (string $nom) use ($coll): array {
            $pipeline = [
                ['$match' => ['nom' => $nom]],
                ['$addFields' => ['day' => ['$dateToString' => ['format'=> '%Y-%m-%d', 'date'=> '$dateCreation','timezone' => 'UTC',]]]],
                ['$group' => ['_id'   => '$day', 'total' => ['$sum' => '$valeur']]],
                ['$sort' => ['_id' => 1]],
            ];
            $cursor = $coll->aggregate($pipeline, ['typeMap' => ['root' => 'array', 'document' => 'array'],]);

            $series = [];
            foreach ($cursor as $doc) {
                // $doc est un array: ['_id' => 'YYYY-MM-DD', 'total' => <int>]
                if (!isset($doc['_id'])) { continue; }
                $series[$doc['_id']] = (int) ($doc['total'] ?? 0);
            }
            return $series;
        };

        // covoiturages créés / jour
        $ridesPerDay   = $buildSeries('rides_created');
        // Crédits gagnés / jour (déjà cumulés dans les docs)
        $creditsPerDay = $buildSeries('credits_earned');

        // --- Total cumulé des crédits plateforme ---
        $cursor = $coll->aggregate([
            ['$match' => ['nom' => 'credits_earned']],
            ['$group' => ['_id' => null, 'sum' => ['$sum' => '$valeur']]],
        ], [
            'typeMap' => ['root' => 'array', 'document' => 'array'],
        ]);

        $creditsTotal = 0;
        foreach ($cursor as $doc) {
            $creditsTotal = (int) ($doc['sum'] ?? 0);
        }

        return $this->render('utilisateurs/admin.html.twig', [
            'rides_per_day'    => $ridesPerDay,
            'credits_per_day'  => $creditsPerDay,
            'credits_total'    => $creditsTotal,
        ]);
    }

    // ---------- Gestion des utilisateurs (inchangée) ----------
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
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $hash = $this->passwordHasher->hashPassword($user, $plain);
                $user->setPassword($hash);
            }
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
        if (! $this->isCsrfTokenValid('delete-user-' . $utilisateur->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token invalide');
        }

        $this->manager->remove($utilisateur);
        $this->manager->flush();

        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('admin_utilisateurs');
    }

    #[Route('/admin/utilisateurs/new', name: 'admin_utilisateurs_new', methods: ['GET','POST'])]
    public function newUser(Request $request): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if (!$plain) {
                $this->addFlash('danger', 'Vous devez renseigner un mot de passe.');
            } else {
                $hash = $this->passwordHasher->hashPassword($user, $plain);
                $user->setPassword($hash);
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
