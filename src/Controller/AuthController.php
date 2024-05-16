<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'app_auth')]
class AuthController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'app_auth_register', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data["username"];
        $password = $data["password"];
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($user) {
            return new JsonResponse([
                'message' => 'User already exists',
                'details' => [
                    'error_code' => 'USER_ALREADY_EXISTS',
                    'description' => 'The username provided already exists in the system.'
                ]
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $user = new User();
        $user->setUsername($username);
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return new JsonResponse([
            'message' => 'User successfully created',
            'details' => [
                'user_id' => $user->getId(),
                'username' => $user->getUsername(),
            ]
        ], JsonResponse::HTTP_CREATED);
    }
}
