<?php
// src/Controller/Api/UtilisateurController.php
namespace App\Controller\Api;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    JsonResponse, Request, Response
};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\{
    SerializerInterface, Normalizer\AbstractNormalizer
};
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\{
    CurrentUser, IsGranted
};

#[Route('/api/utilisateurs', name: 'api_utilisateurs_')]
class UtilisateurController extends AbstractController
{
    private int $creditInitial;

    public function __construct(
        private EntityManagerInterface      $manager,
        private SerializerInterface         $serializer,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $hasher,
        private UtilisateurRepository       $repo,
        int                                 $creditInitial
    ) {
        $this->creditInitial = $creditInitial;
    }
    /**
     * Inscription (POST /api/utilisateurs)
     */
    #[Route('', name:'inscription', methods:['POST'])]
    public function inscrire(Request $req): JsonResponse
    {
        $user = $this->serializer->deserialize(
            $req->getContent(),
            Utilisateur::class,
            'json'
        );

        // validation
        $errors = $this->validator->validate($user);
        if (count($errors)>0) {
            return $this->json(
                ['errors'=>(string)$errors],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // hash + defaults
        $user->setPassword(
            $this->hasher->hashPassword($user, $user->getPassword())
        );
        $user->setRoles(['ROLE_USER']);
        $user->setApiToken(bin2hex(random_bytes(16)));
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setCredit($this->creditInitial);

        try {
            $this->manager->persist($user);
            $this->manager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(
                ['error'=>'Email déjà utilisé'],
                Response::HTTP_CONFLICT
            );
        }

        return $this->json(
            [
                'utilisateur'=> $user->getUserIdentifier(),
                'apiToken'   => $user->getApiToken(),
                'roles'      => $user->getRoles(),
            ],
            Response::HTTP_CREATED,
            ['Location'=> $this->generateUrl('api_utilisateurs_profil', [], UrlGeneratorInterface::ABSOLUTE_URL)]
        );
    }

    /**
     * Connexion (POST /api/utilisateurs/connexion)
     */
    #[Route('/connexion', name:'connexion', methods:['POST'])]
    public function connexion(Request $req): JsonResponse
    {
        $data = json_decode($req->getContent(), true);
        if (empty($data['email'] ?? '') || empty($data['password'] ?? '')) {
            return $this->json(['error'=>'email & password requis'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->repo->findOneBy(['email'=>$data['email']]);
        if (!$user || !$this->hasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error'=>'Identifiants invalides'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'utilisateur'=> $user->getUserIdentifier(),
            'apiToken'   => $user->getApiToken(),
            'roles'      => $user->getRoles(),
        ]);
    }

    /**
     * Déconnexion (POST /api/utilisateurs/deconnexion)
     */
    #[Route('/deconnexion', name:'deconnexion', methods:['POST'])]
    public function deconnexion(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Profil (GET /api/utilisateurs/profil)
     */
    #[Route('/profil', name:'profil', methods:['GET'])]
    #[IsGranted('ROLE_USER')]
    public function profil(#[CurrentUser] Utilisateur $user): JsonResponse
    {
        return $this->json([
            'nom'           => $user->getNom(),
            'prenom'        => $user->getPrenom(),
            'email'         => $user->getEmail(),
            'ville'         => $user->getVille(),
            'dateNaissance' => $user->getDateNaissance() 
                                  ? $user->getDateNaissance()->format('Y-m-d') 
                                  : null,
            'credit'        => $user->getCredit(),
            'createdAt' => $user->getCreatedAt()->format(\DateTime::ATOM),
        ]);
    }
    /**
     * Modifier profil (PUT /api/utilisateurs/profil)
     */
    #[Route('/profil', name:'modifier', methods:['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function modifier(#[CurrentUser] Utilisateur $user, Request $req): JsonResponse
    {
        $this->serializer->deserialize(
            $req->getContent(),
            Utilisateur::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
        );

        $data = json_decode($req->getContent(), true);
        if (!empty($data['password'])) {
            $user->setPassword($this->hasher->hashPassword($user, $data['password']));
        }

        $user->setUpdatedAt(new \DateTimeImmutable());
        $errors = $this->validator->validate($user);
        if (count($errors)>0) {
            return $this->json(['errors'=>(string)$errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Supprimer utilisateur (DELETE /api/utilisateurs/{id})
     */
    #[Route('/{id}', name:'supprimer', methods:['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimer(int $id): JsonResponse
    {
        $user = $this->repo->find($id);
        if (!$user) {
            return $this->json(['error'=>"Utilisateur #{$id} introuvable"], Response::HTTP_NOT_FOUND);
        }
        $this->manager->remove($user);
        $this->manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
