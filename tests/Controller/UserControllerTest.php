<?php

namespace App\Test\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function loggedAsAdmin()
    {
        $this->getEntityAdmin();
        $user = static::getContainer()->get(UserRepository::class)->findOneByUsername('Admin');
        $this->client->loginUser($user);
    }

    public function loggedAsUser()
    {
        $user = static::getContainer()->get(UserRepository::class)->findOneByUsername('User');
        $this->client->loginUser($user);
    }

    public function getEntityUser()
    {
        $user = (new User())
            ->setUsername('User')
            ->setRoles(['ROLE_USER'])
            ->setPassword('password')
            ->setEmail('mailTest@mail.com');
        $this->repository->save($user, true);
    }

    public function getEntityAdmin()
    {
        $user = (new User())
            ->setUsername('Admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('password')
            ->setEmail('AdminMailTest@mail.com');
        $this->repository->save($user, true);
    }

    public function removeUser($name)
    {
        $this->em->remove($this->em->getRepository(User::class)->findOneByUsername($name));
        $this->em->flush();
    }

    public function testUsersListWithAuthorizedAccess(): void
    {
        $this->loggedAsAdmin();

        $this->client->request(Request::METHOD_GET, 'user/list');
        $this->assertResponseStatusCodeSame(200);
        $this->assertRouteSame('app_user_index');

        $this->removeUser('Admin');
    }

    public function testUsersListUnAuthorizedAccess()
    {
        $this->getEntityUser();

        $this->loggedAsUser();

        $this->client->request(Request::METHOD_GET, 'user/list');
        $this->assertResponseStatusCodeSame(403);

        $this->removeUser('User');
    }

    public function testCreateUser(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, 'user/create');
        $this->assertRouteSame('app_user_new');

        $form = $crawler->filter('form')->form([
            'user[username]' => 'TestingUserName',
            'user[roles]' => "Utilisateur",
            'user[plainPassword][first]' => 'password',
            'user[plainPassword][second]' => 'password',
            'user[email]' => 'testMail@mail.com'
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-success', 'L\'utilisateur a bien été créé !');

        $this->removeUser('TestingUserName');
    }

    public function testCreateAdmin(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, 'user/create');
        $this->assertRouteSame('app_user_new');

        $form = $crawler->filter('form')->form([
            'user[username]' => 'CreateAdminForTest',
            'user[roles]' => "Admin",
            'user[plainPassword][first]' => 'password',
            'user[plainPassword][second]' => 'password',
            'user[email]' => 'TestAdminMail@mail.com'
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-success', 'L\'utilisateur a bien été créé !');

        $this->removeUser('CreateAdminForTest');
    }

    public function testEditUser(): void
    {
        $this->loggedAsAdmin();

        $this->getEntityUser();

        $toEditUser = static::getContainer()->get(UserRepository::class)->findOneByUsername('User');

        $crawler = $this->client->request(Request::METHOD_GET, '/user/' . $toEditUser->getId() . '/edit');

        $form = $crawler->filter('form')->form([
            'user[username]' => 'test User edit name',
            'user[roles]' => "Utilisateur",
            'user[plainPassword][first]' => 'password',
            'user[plainPassword][second]' => 'password',
            'user[email]' => 'test@mail.com'
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-success', 'L\'utilisateur a bien été modifié !');

        $this->removeUser('test User edit name');
        $this->removeUser('Admin');
    }

    public function testEditRoleAdmin(): void
    {
        $this->loggedAsAdmin();

        $this->getEntityUser();

        $toEditUser = static::getContainer()->get(UserRepository::class)->findOneByUsername('User');

        $crawler = $this->client->request(Request::METHOD_GET, '/user/' . $toEditUser->getId() . '/edit');

        $form = $crawler->filter('form')->form([
            'user[username]' => 'test User edit name',
            'user[roles]' => "Admin",
            'user[plainPassword][first]' => 'password',
            'user[plainPassword][second]' => 'password',
            'user[email]' => 'test@mail.com'
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-success', 'L\'utilisateur a bien été modifié !');

        $this->removeUser('test User edit name');
        $this->removeUser('Admin');
    }

    public function testDeleteUser(): void
    {
        $this->loggedAsAdmin();
        $this->getEntityUser();

        $urlGenerator = $this->client->getContainer()->get('router.default');

        $crawler = $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_user_index'));

        $form = $crawler->filter('.deleteUser')->last()->form();
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_task_list');
        $this->assertSelectorTextContains('div.alert-success', 'L\'utilisateur a bien été suprimé !');

        // $this->removeUser('User');
        $this->removeUser('Admin');
    }
}