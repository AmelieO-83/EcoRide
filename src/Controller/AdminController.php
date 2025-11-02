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
        // ---------- 1) Rides / jour (MongoDB) ----------
        $mongoDbName = $this->getParameter('mongodb_db') ?? 'ecoride';
        $client = $this->dm->getClient();
        $events = $client->selectDatabase($mongoDbName)->selectCollection('events');

        $pipeline = [
            ['$match' => ['type' => 'ride_created']],
            ['$addFields' => [
                'day' => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$createdAt']]
            ]],
            ['$group' => ['_id' => '$day', 'count' => ['$sum' => 1]]],
            ['$sort'  => ['_id' => 1]],
        ];

        $ridesPerDay = [];
        foreach ($events->aggregate($pipeline) as $doc) {
            $ridesPerDay[$doc->_id] = (int)$doc->count;
        }

        // ---------- 2) Crédits / jour (MySQL) ----------
        // Ici on considère que la plateforme gagne 'fraisPlateforme' par participation confirmée
        $fee = $this->frais->getPlateforme(); // = 2 d'après ton services.yaml

        $conn = $this->manager->getConnection();
        $rows = $conn->fetchAllAssociative(<<<SQL
            SELECT DATE(c.date) AS day, COUNT(*) AS confirmed_count
            FROM participation p
            JOIN covoiturage c ON c.id = p.covoiturage_id
            WHERE p.confirme = 1
            GROUP BY day
            ORDER BY day
        SQL);

        $creditsPerDay = [];
        foreach ($rows as $r) {
            $creditsPerDay[$r['day']] = (int)$r['confirmed_count'] * $fee;
        }

        // ---------- 3) Chiffre du jour ----------
        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $gagneAujourdhui = $creditsPerDay[$today] ?? 0;

        return $this->render('utilisateurs/admin.html.twig', [
            'gagne_aujourdhui' => $gagneAujourdhui,
            'rides_per_day'    => $ridesPerDay,
            'credits_per_day'  => $creditsPerDay,
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
