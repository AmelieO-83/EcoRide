<?php
// src/Controller/NotificationController.php
namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, JsonResponse, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/notifications', name: 'api_notifications_')]
#[IsGranted('ROLE_ADMIN')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationRepository      $notificationRepository,
        private EntityManagerInterface      $manager,
        private SerializerInterface         $serializer
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $modele = $this->notificationRepository->findAll();
        $json = $this->serializer->serialize(
            $modele,
            'json',
            ['groups'=>['notification:read']]
        );
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $modele = new Notification();
        $modele
            ->setType(
                \App\Enum\NotificationType::from($data['type'])
            )
            ->setTitre($data['titre'])
            ->setContenu($data['contenu']);

        $this->manager->persist($modele);
        $this->manager->flush();

        $json = $this->serializer->serialize(
            $modele,
            'json',
            ['groups'=>['notification:read']]
        );
        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $modele = $this->notificationRepository->find($id);
        if (!$modele) {
            return $this->json(['error'=>'Introuvable'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        $modele
            ->setTitre($data['titre'] ?? $modele->getTitre())
            ->setContenu($data['contenu'] ?? $modele->getContenu());
        $this->manager->flush();

        $json = $this->serializer->serialize(
            $modele,
            'json',
            ['groups'=>['notification:read']]
        );
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $modele = $this->notificationRepository->find($id);
        if (!$modele) {
            return $this->json(['error'=>'Introuvable'], Response::HTTP_NOT_FOUND);
        }
        $this->manager->remove($modele);
        $this->manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
