<?php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/client')]
class ClientController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $clients = $this->entityManager->getRepository(Client::class)->findAll();

        $data = [];

        foreach ($clients as $client) {
            $data[] = [
                'id' => $client->getId(),
                'name' => $client->getName(),
                'lastName' => $client->getLastName(),
                'city' => $client->getCity(),
                'category' => $client->getCategory(),
                'age' => $client->getAge(),
                'active' => $client->getActive()
            ];
        }

        return $this->json($data);
    }

    #[Route('/', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client = new Client();
        $client->setName($data['name']);
        $client->setLastName($data['lastName']);
        $client->setCity($data['city']);
        $client->setCategory($data['category']);
        $client->setAge($data['age']);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Client created',
            'details' => [
                'description' => 'The client has been successfully created.'
            ]
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $clientRepository = $this->entityManager->getRepository(Client::class);
        $client = $clientRepository->find($id);

        if (!$client) {
            return new JsonResponse([
                'message' => 'Client not found',
                'details' => [
                    'error_code' => 'CLIENT_NOT_FOUND',
                    'description' => 'The requested client was not found in the system.'
                ]
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $mapping = [
            'name' => 'setName',
            'lastName' => 'setLastName',
            'city' => 'setCity',
            'category' => 'setCategory',
            'age' => 'setAge',
            'active' => 'setActive'
        ];

        foreach ($mapping as $field => $setter) {
            if (isset($data[$field])) {
                $client->$setter($data[$field]);
            }
        }

        $client->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Client updated',
            'details' => [
                'description' => 'The client information has been successfully updated.'
            ]
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $clientRepository = $this->entityManager->getRepository(Client::class);
        $client = $clientRepository->find($id);

        if (!$client) {
            return new JsonResponse([
                'message' => 'Client not found',
                'details' => [
                    'error_code' => 'CLIENT_NOT_FOUND',
                    'description' => 'The requested client was not found in the system.'
                ]
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($client);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Client deleted',
            'details' => [
                'description' => 'The client has been successfully deleted from the system.'
            ]
        ], JsonResponse::HTTP_OK);
    }
    
    #[Route('/search', methods: ['GET'])]
    public function advancedSearch(Request $request): JsonResponse
    {

        $params = $request->query->all();
        $queryBuilder = $this->entityManager->getRepository(Client::class)->createQueryBuilder('c');

        foreach ($params as $field => $value) {
            switch ($field) {
                case 'id':
                    $queryBuilder->andWhere("c.$field = :$field")->setParameter($field, $value);
                    break;
                case 'name':
                    $queryBuilder->andWhere("c.$field LIKE :$field")->setParameter($field, "%$value%");
                    break;
                case 'lastName':
                    $queryBuilder->andWhere("c.$field LIKE :$field")->setParameter($field, "%$value%");
                    break;
                case 'city':
                    $queryBuilder->andWhere("c.$field LIKE :$field")->setParameter($field, "%$value%");
                    break;
                case 'category':
                    $queryBuilder->andWhere("c.$field = :$field")->setParameter($field, $value);
                    break;
                case 'age':
                    if (isset($value['greaterThan'])) {
                        $queryBuilder->andWhere("c.$field > :$field")->setParameter($field, $value['greaterThan']);
                    }
                    if (isset($value['lessThan'])) {
                        $queryBuilder->andWhere("c.$field < :$field")->setParameter($field, $value['lessThan']);
                    }
                    break;
                case 'active':
                    $queryBuilder->andWhere("c.$field = :$field")->setParameter($field, $value);
                    break;
            }
        }

        $clients = $queryBuilder->getQuery()->getResult();

        $data = [];
        foreach ($clients as $client) {
            $data[] = [
                'name' => $client->getName(),
                'lastName' => $client->getLastName(),
                'city' => $client->getCity(),
                'category' => $client->getCategory(),
                'age' => $client->getAge(),
                'active' => $client->getActive()
            ];
        }

        return $this->json($data);
    }
}
