<?php

namespace App\Test\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private TaskRepository $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Task::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }
    
    /**
     * Insert into database an Admin
     *
     * @return void
     */
    public function getEntityAdmin()
    {
        $user = (new User())
            ->setUsername('Admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('0000')
            ->setEmail('AdminMailTest@mail.com');
        static::getContainer()->get(UserRepository::class)->save($user, true);
    }
    
    /**
     * Logged us as an Admin
     *
     * @return void
     */
    public function loggedAsAdmin()
    {
        $user = static::getContainer()->get(UserRepository::class)->findOneByUsername('Admin');
        $this->client->loginUser($user);
    }
    
    /**
     * Logged us as a User
     *
     * @return void
     */
    public function loggedAsUser()
    {
        $user = (new User())
            ->setUsername('User')
            ->setRoles(['ROLE_USER'])
            ->setPassword('0000')
            ->setEmail('mailTest@mail.com');
        static::getContainer()->get(UserRepository::class)->save($user, true);
        
        $user = static::getContainer()->get(UserRepository::class)->findOneByUsername('User');
        $this->client->loginUser($user);
    }
    
    /**
     * Remove user from database
     *
     * @param  mixed $name
     * @return void
     */
    public function removeUser($name)
    {
        $this->em->remove($this->em->getRepository(User::class)->findOneByUsername($name));
        $this->em->flush();
    }
    
    /**
     * Insert into database an anonymous task
     *
     * @return void
     */
    public function getAnonymousTask()
    {
        $task = new Task();
        $task->setCreatedAt(new \DateTime('Europe/Paris'));
        $task->setTitle('titre test');
        $task->setContent('Contenu test');
        $task->setIsDone(false);
        $task->setUser(null);
        $this->repository->save($task, true);
    }
    
    /**
     * Insert into database an linked task to an admin
     *
     * @return void
     */
    public function getTaskLinkedToAdmin()
    {
        $task = new Task();
        $task->setCreatedAt(new \DateTime('Europe/Paris'));
        $task->setTitle('AdminTask');
        $task->setContent('Contenu test');
        $task->setIsDone(false);
        $task->setUser($this->em->getRepository(User::class)->findOneByUsername('Admin'));
        $this->repository->save($task, true);
    }
    
    /**
     * Remove from database a task
     *
     * @param  mixed $title
     * @return void
     */
    public function removeTask($title)
    {
        $this->em->remove($this->em->getRepository(Task::class)->findOneByTitle($title));
        $this->em->flush();
    }
    
    /**
     * Test the display of task list 
     *
     * @return void
     */
    public function testIndexTask(): void
    {
        $urlGenerator = $this->client->getContainer()->get('router.default');

        $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_task_list'));
        $this->assertResponseStatusCodeSame(200);
        $this->assertRouteSame('app_task_list');
    }
    
    /**
     * Test creating task linked to an admin using form 
     *
     * @return void
     */
    public function testCreateTaskLinkedToAdmin(): void
    {
        $this->getEntityAdmin();
        $this->loggedAsAdmin();

        $crawler = $this->client->request(Request::METHOD_GET, '/task/create');

        $form = $crawler->filter('form')->form([
            'task[createdAt]' => '2023-01-01 12:00:00',
            'task[title]' => 'testTitleTask',
            'task[content]' => 'test Content Task',
            'task[isDone]' => '0'
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-success', 'La tache a bien été créé !');

        $this->removeTask('testTitleTask');
        $this->removeUser('Admin');
    }
    
    /**
     * Test creating anonymous task using form
     *
     * @return void
     */
    // public function testCreateTaskAnonymously()
    // {
    //     $crawler = $this->client->request(Request::METHOD_GET, '/task/create');

    //     $form = $crawler->filter('form')->form([
    //         'task[createdAt]' => '2023-01-01 12:00:00',
    //         'task[title]' => 'testTitleTask',
    //         'task[content]' => 'test Content Task',
    //         'task[isDone]' => '1'
    //     ]);
    //     $this->client->submit($form);

    //     $this->assertResponseRedirects();
    //     $this->client->followRedirect();
    //     $this->assertRouteSame('app_task_list');
    //     $this->assertSelectorTextContains('div.alert-success', 'La tache a bien été créé !');

    //     $this->removeTask('testTitleTask');
    // }
    
    /**
     * Test edit an anonymous task as an admin
     *
     * @return void
     */
    public function testEditTask(): void
    {
        $this->getAnonymousTask();
        $this->getEntityAdmin();
        $this->loggedAsAdmin();

        $toEditTask = static::getContainer()->get(TaskRepository::class)->findOneByTitle('titre test');

        $crawler = $this->client->request(Request::METHOD_GET, '/task/' . $toEditTask->getId() . '/edit');

        $form = $crawler->filter('form')->form([
            'task[createdAt]' => '2023-01-01 14:00:00',
            'task[title]' => 'Edit du titre test',
            'task[content]' => 'Edit du contenu test',
            'task[isDone]' => '1'
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-success', 'La tache a bien été modifié !');

        $this->em->remove($this->em->getRepository(Task::class)->findOneByTitle('Edit du titre test'));
        $this->em->flush();

        $this->removeUser('Admin');
    }
    
    /**
     * Test toggle task from false to true
     *
     * @return void
     */
    public function testToggleTaskToDone()
    {
        $this->getAnonymousTask();

        $urlGenerator = $this->client->getContainer()->get('router.default');

        $crawler = $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_task_list'));
        $this->assertResponseStatusCodeSame(200);

        $form = $crawler->selectButton('Marquer comme faite')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-success', 'La tache à bien été marqué comme faite !');
    }
    
    /**
     * Test toggle task from true to false
     *
     * @return void
     */
    public function testToggleTaskToFalse()
    {
        $urlGenerator = $this->client->getContainer()->get('router.default');

        $crawler = $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_task_list'));
        $this->assertResponseStatusCodeSame(200);

        $form = $crawler->selectButton('Marquer non terminée')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-success', 'La tache à bien été marqué comme non terminé !');

        $this->removeTask('titre test');
    }
    
    /**
     * Test delete anonymous task as an admin
     *
     * @return void
     */
    public function testAnonymousTaskDeletedByAnAdmin(): void
    {
        $this->getAnonymousTask();
        $this->getEntityAdmin();
        $this->loggedAsAdmin();

        $urlGenerator = $this->client->getContainer()->get('router.default');

        $crawler = $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_task_list'));

        $form = $crawler->filter('.deleteTask')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-success', 'Admin a bien supprimé la tâche de "Anonyme" !');
    
        $this->removeUser('Admin');
    }
    
    /**
     * Test delete task linked to user
     *
     * @return void
     */
    public function testRemoveTaskLinkedToUser(): void
    {
        $this->getEntityAdmin();
        $this->loggedAsAdmin();
        $this->getTaskLinkedToAdmin();

        $urlGenerator = $this->client->getContainer()->get('router.default');

        $crawler = $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_task_list'));

        $form = $crawler->filter('.deleteTask')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-success', 'La tâche a bien été supprimé !');

        $this->removeUser('Admin');
    }
    
    /**
     * Test invalid remove anonymous task as a user 
     *
     * @return void
     */
    public function testInvalidRemoveTaskAnonymousByUser(): void
    {
        $this->getAnonymousTask();
        $this->loggedAsUser();

        $urlGenerator = $this->client->getContainer()->get('router.default');

        $crawler = $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_task_list'));

        $form = $crawler->filter('.deleteTask')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-danger', 'Vous n\'avez pas les droits pour supprimer une tache "Anonyme" !');
    
        $this->removeTask('titre test');
        $this->removeUser('User');
    }
    
    /**
     * Invalid test of remove task linked to user from another user
     *
     * @return void
     */
    public function testInvalidRemoveTaskLinkedToAdminByUser(): void
    {
        $this->loggedAsUser();
        $this->getEntityAdmin();
        $this->getTaskLinkedToAdmin();

        $urlGenerator = $this->client->getContainer()->get('router.default');

        $crawler = $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_task_list'));

        $form = $crawler->filter('.deleteTask')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-danger', 'Vous n\'êtes pas propriétaire de cette tâche, vous ne pouvez donc pas la supprimer !');
    
        $this->removeTask('AdminTask');
        $this->removeUser('User');
        $this->removeUser('Admin');
    }
}