<?php 

namespace App\Test\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;


class UserTest extends KernelTestCase
{
    public function getEntity(): User
    {
        return (new User())
        ->setUsername('username test')
        ->setRoles(['ROLE_USER'])
        ->setPassword('password')
        ->setEmail('mailTest@mail.com');
    }

    public function assertHasErrors(User $user, int $number = 0) 
    {
        self::bootKernel();
        $errors = self::getContainer()->get('validator')->validate($user);
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testValidUserEntity()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }

    public function testInvalidBlankEmailEntity()
    {
        $this->assertHasErrors($this->getEntity()->setEmail(''), 1);
    }

    Public function testInvalidLenghEmailEntity()
    {
        $maxSizeEmail = 'SauspendisserateeuismodzeratazVivamusaexaduialiberos@mail.com';
        $this->assertHasErrors($this->getEntity()->setEmail($maxSizeEmail), 1);
    }

    public function testInvalidBlankUserNameEntity()
    {
        $this->assertHasErrors($this->getEntity()->setUsername(''), 1);

    }

    Public function testInvalidLenghUserNameEntity()
    {
        $maxSizeUsername = 'aNam et orci vehicula metus semper imperdiet. Suspendisse vulputate feugiat nunc non mollis. Donec gravida odio vel porta sagittis. Duis sed enim sed turpis cursus sagittis blandit.';
        $this->assertHasErrors($this->getEntity()->setUsername($maxSizeUsername), 1);
    }
}

// vendor/bin/phpunit --filter UserTest