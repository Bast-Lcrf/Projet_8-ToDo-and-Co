<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{ 
    /**
     * This controller allow us to render the home page
     *
     * @return Response
     */
    #[Route('/home', name: 'app_default')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
            'users' => $this->getUser()
        ]);
    }
}
