<?php
// src/Controller/VoitureController.php
namespace App\Controller;

use App\Entity\Voiture;
use App\Repository\MarqueRepository;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/voitures', name: 'api_voitures_')]
class VoitureController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface   $manager,
        private SerializerInterface      $serializer,
        private ValidatorInterface $validator,
        private VoitureRepository        $repository,
        private MarqueRepository $marqueRepository
    ){}

    /**
     * GET /api/voitures
     * Liste toutes les voitures du propriétaire courant.
     */
    #[Route('', name:'list', methods:['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(#[CurrentUser] $proprietaire): JsonResponse
    {
        $list = $this->repository->findByProprietaire($proprietaire);
        $json = $this->serializer->serialize($list, 'json', ['groups'=>['voiture:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * GET /api/voitures/{id}
     */
    #[Route('/{id}', name:'show', methods:['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(int $id, #[CurrentUser] $proprietaire): JsonResponse
    {
        $voiture = $this->repository->find($id);
        if (!$voiture) {
            return $this->json(['error'=>'Voiture introuvable'], Response::HTTP_NOT_FOUND);
        }
        if ($voiture->getProprietaire() !== $proprietaire && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error'=>'Interdit'], Response::HTTP_FORBIDDEN);
        }
        $json = $this->serializer->serialize($voiture, 'json', ['groups'=>['voiture:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * POST /api/voitures
     * Crée une nouvelle voiture pour le propriétaire courant.
     */
    #[Route('', name:'create', methods:['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, #[CurrentUser] $proprietaire): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
 
        // 1) Récupère l'entité Marque
        $idMarque = isset($data['marque']) ? (int) $data['marque'] : 0;
        $marque   = $this->marqueRepository->find($idMarque);
        if (null === $marque) {
            return $this->json(
                ['error' => "Marque #{$idMarque} introuvable"],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
        // 2) Désérialisation des autres champs (sans "marque")
        $voiture = $this->serializer->deserialize(
            $request->getContent(),
            Voiture::class,
            'json',
            [
              AbstractNormalizer::GROUPS => ['voiture:write'],
              AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true,
            ]
        );

        $voiture
            ->setProprietaire($proprietaire)
            ->setMarque($marque);

        $errors = $this->validator->validate($voiture);
        if (count($errors)>0) {
            return $this->json(['errors'=>(string)$errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->manager->persist($voiture);
        $this->manager->flush();

        $json = $this->serializer->serialize($voiture, 'json', ['groups'=>['voiture:read']]);
        return new JsonResponse(
            $json,
            Response::HTTP_CREATED,
            ['Location'=>$this->generateUrl('api_voitures_show',['id'=>$voiture->getId()])],
            true
        );
    }

    /**
     * PUT /api/voitures/{id}
     * Met à jour une voiture (propriétaire ou admin).
     */
    #[Route('/{id}', name:'update', methods:['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id, Request $request, #[CurrentUser] $proprietaire): JsonResponse
    {
        $voiture = $this->repository->find($id);
        if (!$voiture) {
            return $this->json(['error'=>'Voiture introuvable'], Response::HTTP_NOT_FOUND);
        }
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        if ($voiture->getProprietaire() !== $proprietaire && !$isAdmin) {
            return $this->json(['error'=>'Interdit'], Response::HTTP_FORBIDDEN);
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Voiture::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $voiture, 'groups'=>['voiture:write']]
        );

        $errors = $this->validator->validate($voiture);
        if (count($errors)>0) {
            return $this->json(['errors'=>(string)$errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * DELETE /api/voitures/{id}
     * Supprime une voiture (propriétaire ou admin).
     */
    #[Route('/{id}', name:'delete', methods:['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id, #[CurrentUser] $proprietaire): JsonResponse
    {
        $voiture = $this->repository->find($id);
        if (!$voiture) {
            return $this->json(['error'=>'Voiture introuvable'], Response::HTTP_NOT_FOUND);
        }
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        if ($voiture->getProprietaire() !== $proprietaire && !$isAdmin) {
            return $this->json(['error'=>'Interdit'], Response::HTTP_FORBIDDEN);
        }
        $this->manager->remove($voiture);
        $this->manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
