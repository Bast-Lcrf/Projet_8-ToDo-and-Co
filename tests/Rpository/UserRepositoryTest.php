<?php

namespace App\Test\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRepositoryTest extends KernelTestCase
{
    public function testSaveUser()
    {
        self::bootKernel();

        $userRepository = new UserRepository(static::getContainer()->get(ManagerRegistry::class));
        
        $hash = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = (new User())
            ->setUsername('UserTest')
            ->setEmail('UserMailTest@mail.com')
            ->setRoles(['ROLE_USER'])
            ->setPlainPassword('0000');
        $pass = $hash->hashPassword($user, $user->getPlainPassword());
        $user->setPassword($pass);

        $this->assertInstanceOf(User::class, $user);

        $userRepository->save($user, true);

        $this->assertNotNull($userRepository->findOneByUsername('UserTest'));
    }

    public function testRemoveUser()
    {
        self::bootKernel();

        $userRepository = new UserRepository(static::getContainer()->get(ManagerRegistry::class));
        
        $user = $userRepository->findOneByUsername('UserTest');

        $this->assertInstanceOf(User::class, $user);

        $userRepository->remove($user, true);

        $this->assertNull($userRepository->findOneByUsername('UserTest'));
    }
}