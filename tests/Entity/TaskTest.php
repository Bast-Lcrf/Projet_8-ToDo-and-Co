<?php 

namespace App\Test\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class TaskTest extends KernelTestCase
{
    
    /**
     * Insert into database a task 
     *
     * @return Task
     */
    public function getEntity(): Task
    {
        return (new Task())
            ->setCreatedAt(new \DateTime)
            ->setTitle('Titre test de la tache')
            ->setContent('Contenu test de la tache')
            ->setIsDone(1)
            ->setUser(new User);
    }
    
    /**
     * Display message error
     * Error count
     *
     * @param  Task $task
     * @param  int $number
     * @return void
     */
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
    
    /**
     * Test if entity is valid
     *
     * @return void
     */
    public function testValidTaskEntity()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }
    
    /**
     * Test invalid length title
     *
     * @return void
     */
    public function testInvalidLengthTitle()
    {
        $maxSizeTitle = 'Adonec tristique consequat quam, a elementum enim interdum tincidunt. Vestibulum lacinia tempor tortor, ac elementum tortor commodo ac. Nullam at euismod mi, eget congue augue. Aenean vitae ipsum a quam sollicitudin molestie. Nulla elementum luctus sapien.';
        $this->assertHasErrors($this->getEntity()->setTitle($maxSizeTitle), 1);
    }
    
    /**
     * Test: title can't be blank
     *
     * @return void
     */
    public function testInvalidBlankTitle()
    {
        $this->assertHasErrors($this->getEntity()->setTitle(''), 1);
    }
    
    /**
     * Test: content can't be blank
     *
     * @return void
     */
    public function testInvalidBlankContent()
    {
        $this->assertHasErrors($this->getEntity()->setContent(''), 1);
    }
}