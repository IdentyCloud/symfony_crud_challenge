<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'app_auth')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'app_auth_register')]
    public function index(Request $request): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller REGISTER!',
            'path' => 'src/Controller/AuthController.php',
        ]);
    }
}
