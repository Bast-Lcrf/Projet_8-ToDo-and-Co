<?php

namespace App\Test\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Symfony\Component\BrowserKit\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private TaskRepository $repository;
    private string $path = '/task';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Task::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndexTask(): void
    {
        // $crawler = $this->client->request('GET', $this->path);

        // self::assertResponseStatusCodeSame(200);
        // self::assertPageTitleContains('Task index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
        $urlGenerator = $this->client->getContainer()->get('router.default');
        $this->client->request(HttpFoundationRequest::METHOD_GET, $urlGenerator->generate('app_task_list'));
        $this->assertResponseStatusCodeSame(200);
    }

    public function testNewTask(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'task[createdAt]' => '01/01/2023',
            'task[title]' => 'Testing',
            'task[content]' => 'Testing',
            'task[isDone]' => '1',
            'task[user_id]' => '1'
        ]);

        self::assertResponseRedirects('/task/new');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Task();
        $fixture->setCreatedAt(new \DateTime('Europe/Paris'));
        $fixture->setTitle('My Title');
        $fixture->setContent('My Title');
        $fixture->setIsDone('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Task');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Task();
        $fixture->setCreatedAt(new \DateTime('Europe/Paris'));
        $fixture->setTitle('My Title');
        $fixture->setContent('My Title');
        $fixture->setIsDone('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'task[createdAt]' => 'Something New',
            'task[title]' => 'Something New',
            'task[content]' => 'Something New',
            'task[isDone]' => 'Something New',
        ]);

        self::assertResponseRedirects('/task/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getContent());
        self::assertSame('Something New', $fixture[0]->isIsDone());
    }

    public function testRemoveTask(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Task();
        $fixture->setCreatedAt(new \DateTime('Europe/Paris'));
        $fixture->setTitle('My Title');
        $fixture->setContent('My Title');
        $fixture->setIsDone('1');
        $fixture->setUser(new User);

        $this->repository->save($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/task');
    }
}
