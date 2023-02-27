<?php 

namespace App\Test\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;


class TaskTest extends KernelTestCase
{

    public function getEntity(): Task
    {
        return (new Task())
            ->setCreatedAt(new \DateTime)
            ->setTitle('Titre test de la tache')
            ->setContent('Contenu test de la tache')
            ->setIsDone(1)
            ->setUser(new User);
    }

    public function assertHasErrors(Task $task, int $number = 0)
    {
        self::bootKernel();
        $errors = self::getContainer()->get('validator')->validate($task);
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testValidTaskEntity()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }

    public function testInvalidLentghTitle()
    {
        $maxSizeTitle = 'Adonec tristique consequat quam, a elementum enim interdum tincidunt. Vestibulum lacinia tempor tortor, ac elementum tortor commodo ac. Nullam at euismod mi, eget congue augue. Aenean vitae ipsum a quam sollicitudin molestie. Nulla elementum luctus sapien.';
        $this->assertHasErrors($this->getEntity()->setTitle($maxSizeTitle), 1);
    }

    public function testInvalidBlankTitle()
    {
        $this->assertHasErrors($this->getEntity()->setTitle(''), 1);
    }

    public function testInvalidBlankContent()
    {
        $this->assertHasErrors($this->getEntity()->setContent(''), 1);
    }
}

// vendor/bin/phpunit --filter TaskTest
// XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text