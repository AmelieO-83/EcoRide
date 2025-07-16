<?php
// src/Controller/MarqueController.php
namespace App\Controller;

use App\Entity\Marque;
use App\Repository\MarqueRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/marques', name: 'api_marques_')]
class MarqueController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private SerializerInterface    $serializer,
        private ValidatorInterface      $validator,
        private MarqueRepository       $repository
    ){}

    /**
     * GET  /api/marques
     * Retourne la liste de toutes les marques.
     */
    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(): JsonResponse
    {
        $all   = $this->repository->findAll();
        $json  = $this->serializer->serialize($all, 'json', ['groups'=>['marque:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * GET  /api/marques/{id}
     * Affiche une marque par son ID.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(int $id): JsonResponse
    {
        $marque = $this->repository->find($id);
        if (!$marque) {
            return $this->json(['error'=>'Marque introuvable'], Response::HTTP_NOT_FOUND);
        }
        $json = $this->serializer->serialize($marque, 'json', ['groups'=>['marque:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * POST /api/marques
     * Crée une nouvelle marque.
     */
    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $req): JsonResponse
    {
        $marque = $this->serializer->deserialize(
            $req->getContent(),
            Marque::class,
            'json',
            ['groups'=>['marque:write']]
        );

        // Validation
        $errors = $this->validator->validate($marque);
        if (count($errors) > 0) {
            return $this->json(['errors'=> (string)$errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Gestion de l'unicité
        try {
            $this->manager->persist($marque);
            $this->manager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(
                ['error'=>'Ce libellé de marque existe déjà'],
                Response::HTTP_CONFLICT
            );
        }

        $json = $this->serializer->serialize($marque, 'json', ['groups'=>['marque:read']]);
        return new JsonResponse(
            $json,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('api_marques_show', ['id'=>$marque->getId()])],
            true
        );
    }

    /**
     * PUT /api/marques/{id}
     * Met à jour le libellé d’une marque.
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(int $id, Request $req): JsonResponse
    {
        $marque = $this->repository->find($id);
        if (!$marque) {
            return $this->json(['error'=>'Marque introuvable'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize(
            $req->getContent(),
            Marque::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $marque, 'groups'=>['marque:write']]
        );

        $errors = $this->validator->validate($marque);
        if (count($errors) > 0) {
            return $this->json(['errors'=> (string)$errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->manager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(
                ['error'=>'Ce libellé de marque existe déjà'],
                Response::HTTP_CONFLICT
            );
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * DELETE /api/marques/{id}
     * Supprime une marque.
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        $marque = $this->repository->find($id);
        if (!$marque) {
            return $this->json(['error'=>'Marque introuvable'], Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($marque);
        $this->manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
