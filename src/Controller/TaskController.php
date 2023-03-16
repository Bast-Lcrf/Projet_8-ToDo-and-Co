<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/task')]
class TaskController extends AbstractController
{   
    /**
     * This controller displays the tasks list
     *
     * @param  mixed $taskRepository
     * @return Response
     */
    #[Route('/list', name: 'app_task_list', methods: ['GET'])] 
    public function index(TaskRepository $taskRepository): Response
    {
        return $this->render('task/list.html.twig', [
            'tasks' => $taskRepository->findAll(),
        ]);
    }
  
    /**
     * This controller allow us to create a new task linked with his author
     *
     * @param  Request $request
     * @param  TaskRepository $taskRepository
     * @return Response
     */
    #[Route('/create', name: 'app_task_create', methods: ['GET', 'POST'])]  
    public function Create(Request $request, TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $task->setUser($user);
            $taskRepository->save($task, true);

            $this->addFlash(
                'success',
                'La tache a bien été créé !'
            );

            return $this->redirectToRoute('app_task_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/create.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    /**
     * This controller allows us to switch a task from completed to not completed
     * 
     * @param Task $task
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/{id}/toggle', name: 'app_task_toggle', methods: ['GET'])]    
    public function toggleTaskAction(Task $task, EntityManagerInterface $em): Response
    {
        $done = $task->isIsDone();

        if($done == false) {
            $task->setIsDone(true);
            $em->persist($task);
            $em->flush();

            $this->addFlash(
                'success',
                'La tache à bien été marqué comme faite !'
            );
        } else {
            $task->setIsDone(false);
            $em->persist($task);
            $em->flush();

            $this->addFlash(
                'success',
                'La tache à bien été marqué comme non terminé !'
            );
        }

        return $this->redirectToRoute('app_task_list');
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->save($task, true);

            $this->addFlash(
                'success',
                'La tache a bien été modifié !'
            );

            return $this->redirectToRoute('app_task_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_task_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Task $task,
        TaskRepository $taskRepository
    ): Response
    {
        $user = $this->getUser();
        
        if($user == $task->getUser()) {
            if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
                $taskRepository->remove($task, true);
            }
            $this->addFlash(
                'success',
                'La tâche a bien été supprimé !'
            );
        } elseif($task->getUser() == null) {
            if($this->getUser()->getRoles() == ["ROLE_ADMIN","ROLE_USER"]) {
                if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
                    $taskRepository->remove($task, true);
                }
                $this->addFlash(
                    'success',
                    'Admin a bien supprimé la tâche de "Anonyme" !'
                );
            } else {
                $this->addFlash(
                    'error',
                    'Vous n\'avez pas les droits pour supprimer une tache "Anonyme" !'
                );
            }
        } else {
            $this->addFlash(
                'error', 
                'Vous n\'êtes pas propriétaire de cette tâche, vous ne pouvez donc pas la supprimer !'
            );
        }
        return $this->redirectToRoute('app_task_list', [], Response::HTTP_SEE_OTHER);
    }
}
