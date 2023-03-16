<?php

namespace App\Test\Repository;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskRepositoryTest extends KernelTestCase
{
    public function testTaskSave()
    {
        self::bootKernel();

        $taskRepository = new TaskRepository(static::getContainer()->get(ManagerRegistry::class));

        $task = (new Task())
            ->setCreatedAt(new \DateTime('Europe/Paris'))
            ->setTitle('titre de test')
            ->setContent('Contenu de test')
            ->setIsDone(false)
            ->setUser(null);
        
        $this->assertInstanceOf(Task::class, $task);

        $taskRepository->save($task, true);

        $this->assertNotNull($taskRepository->findOneByTitle('titre de test'));
    }

    public function testRemoveTask()
    {
        self::bootKernel();

        $taskRepository = new TaskRepository(static::getContainer()->get(ManagerRegistry::class));

        $task = $taskRepository->findOneByTitle('titre de test');

        $this->assertInstanceOf(Task::class, $task);

        $taskRepository->remove($task, true);

        $this->assertNull($taskRepository->findOneById($task->getId()));
    }
}