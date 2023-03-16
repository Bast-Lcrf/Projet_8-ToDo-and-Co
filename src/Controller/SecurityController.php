<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * This Controller allow us to login
     *
     * @param AuthenticationUtils $authenticationUtils
     * 
     * @return Response
     */
    #[Route('/connexion', name: 'app_security_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }

    /**
     * This controller allow us to logout
     *
     * @return void
     */
    #[Route('/deconnexion', name: 'app_security_logout', methods: ['GET'])]
    public function logout()
    {
        // Nothing to do here..
    }
}
