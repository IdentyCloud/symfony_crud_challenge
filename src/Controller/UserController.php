<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/user')]
class UserController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'active' => $user->getActive()
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getById(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse([
                'message' => 'User not found',
                'details' => [
                    'error_code' => 'USER_NOT_FOUND',
                    'description' => 'The requested user was not found in the system.'
                ]
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $serializedUser = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'active' => $user->getActive()
        ];

        return $this->json($serializedUser);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse([
                'message' => 'User not found',
                'details' => [
                    'error_code' => 'USER_NOT_FOUND',
                    'description' => 'The requested user was not found in the system.'
                ]
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'User deleted',
            'details' => [
                'description' => 'The user has been successfully deleted from the system.'
            ]
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse([
                'message' => 'User not found',
                'details' => [
                    'error_code' => 'USER_NOT_FOUND',
                    'description' => 'The requested user was not found in the system.'
                ]
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $active = $data['active'] ?? null;

        if ($active !== null) {
            $user->setActive((bool)$active);
            $user->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'User updated',
                'details' => [
                    'description' => 'The user information has been successfully updated.'
                ]
            ], JsonResponse::HTTP_OK);
        }

        return new JsonResponse([
            'message' => 'Invalid request',
            'details' => [
                'error_code' => 'INVALID_REQUEST',
                'description' => 'The request made to the server is invalid.'
            ]
        ], JsonResponse::HTTP_BAD_REQUEST);
    }
}
