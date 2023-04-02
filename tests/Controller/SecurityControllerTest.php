<?php

namespace App\Test\Controller;

use App\Controller\DefaultController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $repository;
    private EntityManagerInterface $em;
    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }
    
    /**
     * This method allows us to insert a test user into the database
     *
     * @return void
     */
    public function getEntityUser()
    {
        $user = (new User())
            ->setUsername('User')
            ->setRoles(['ROLE_USER'])
            ->setPassword('password')
            ->setEmail('mailTest@mail.com');
        $this->repository->save($user, true);
    }
    
    /**
     * This method allow us to remove a user from database
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
     * Login test with the form
     *
     * @return void
     */
    public function testLogin()
    {
        $this->getEntityUser();
        $urlGenerator = $this->client->getContainer()->get('router.default');

        $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_security_login'));
        $this->assertResponseStatusCodeSame(200);
        $this->assertRouteSame('app_security_login');

        $crawler = $this->client->request(Request::METHOD_GET, '/connexion');
        $form = $crawler->filter('form')->form([
            '_username' => 'User',
            '_password' => 'password'
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects("", 302);
    }
    
    /**
     * Logout test using the logout button
     *
     * @return void
     */
    public function testLogout()
    {
        $user = static::getContainer()->get(UserRepository::class)->findOneByUsername('User');
        $this->client->loginUser($user);

        $urlGenerator = $this->client->getContainer()->get('router.default');

        $crawler = $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_default'));

        $link = $crawler->filter('.btn-danger')->link();
        $this->client->click($link);
        $this->assertResponseRedirects("", 302);
        
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $this->removeUser('User');
    }
}