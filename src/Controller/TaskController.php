<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Task;
use App\Form\CommentType;
use App\Form\TaskType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tasks = $entityManager->getRepository(Task::class)->findAllWithLongestComment();

        return $this->render(
            'tasks.html.twig', [
                'tasks' => $tasks
            ]
        );
    }

    public function getTask(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);
        $comments = $entityManager->getRepository(Comment::class)->findByTaskId($id);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $user = $this->getUser()) {
            $comment->setAuthor($user);
            $comment->setTask($task);
            $comment->setDate(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirect('/task/' . $id);
        }

        return $this->render(
            'task.html.twig', [
                'form' => $form->createView(),
                'task' => $task,
                'comments' => $comments
            ]
        );
    }

    public function createTask(Request $request)
    {
        if (!$user = $this->getUser()) {
            return $this->redirectToRoute('login');
        }
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task, ['author_id' => $user->getId()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setAuthor($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('tasks');
        }

        return $this->render(
            'newtask.html.twig',
            array('form' => $form->createView())
        );
    }

    public function updateTask(Request $request, $id)
    {
        if (!$user = $this->getUser()) {
            return $this->redirectToRoute('login');
        }
        $entityManager = $this->getDoctrine()->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);
        $form = $this->createForm(TaskType::class, $task, ['author_id' => $user->getId()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('tasks');
        }

        return $this->render(
            'newtask.html.twig',
            array('form' => $form->createView())
        );
    }
}