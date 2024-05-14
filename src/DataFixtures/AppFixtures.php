<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            ['username' => 'username_a', 'password' => 'password_a'],
            ['username' => 'username_b', 'password' => 'password_b'],
            ['username' => 'username_c', 'password' => 'password_c'],
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setUsername($userData['username']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $userData['password']));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
