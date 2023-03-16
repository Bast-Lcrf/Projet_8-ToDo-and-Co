<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * This controller allows us to list all registered users.
     * Restriction : Admins access only
     *
     * @param  UserRepository $userRepository
     * 
     * @return Response
     */
    #[Route('/list', name: 'app_user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour accéder a cette page !')]    
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', [
            'user' => $userRepository->findAll(),
        ]);
    }

    /**
     * This controller allow us to create a new user
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordHasherInterface $hasher
     * 
     * @return void
     */
    #[Route('/create', name: 'app_user_new', methods: ['GET', 'POST'])]   
    public function Create(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des roles
            $roles = $form->get('roles')->getData();
            if($roles == 'Utilisateur') {
                $user->setRoles(['ROLE_USER']);
            } else {
                $user->setRoles(['ROLE_ADMIN']);
            }

            // Récupération et hash du mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            $password = $hasher->hashPassword($user, $plainPassword);
            $user->setPassword($password);
            $user->eraseCredentials();

            // On envoie dans la BDD
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'L\'utilisateur a bien été créé !'
            );

            return $this->redirectToRoute('app_task_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/create.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * This controller allow us edit a user
     * Restriction : Admins access only
     *
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $em
     * @param UserPasswordHasherInterface $hasher
     * 
     * @return void
     */
    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]  
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour accéder a cette page !')]  
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des roles
            $roles = $form->get('roles')->getData();
            if($roles == 'Utilisateur') {
                $user->setRoles(['ROLE_USER']);
            } else {
                $user->setRoles(['ROLE_ADMIN']);
            }

            // Récupération et hash du mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            $password = $hasher->hashPassword($user, $plainPassword);
            $user->setPassword($password);

            // On envoie dans la BDD
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'L\'utilisateur a bien été modifié !'
            );

            return $this->redirectToRoute('app_task_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
 
    /**
     * This controller allow us to delete a user
     * Restricion: Admins access only
     *
     * @param  Request $request
     * @param  User $user
     * @param  UserRepository $userRepository
     * 
     * @return Response
     */
    #[Route('/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour accéder a cette page !')]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }
        $this->addFlash(
            'success',
            'L\'utilisateur a bien été suprimé !'
        );

        return $this->redirectToRoute('app_task_list', [], Response::HTTP_SEE_OTHER);
    }
}
