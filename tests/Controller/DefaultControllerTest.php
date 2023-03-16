<?php

namespace App\Test\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    
    public function testIndex()
    {
        $this->client = static::createClient();

        $urlGenerator = $this->client->getContainer()->get('router.default');

        $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_default'));
        $this->assertResponseStatusCodeSame(200);
        $this->assertRouteSame('app_default');
    }
}