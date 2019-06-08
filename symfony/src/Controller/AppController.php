<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class AppController extends AbstractController
{
    /**
     * Display the main screen
     * @return Response
     */
    public function home(string $week): Response
    {
        /** @var NotebookRepository $notebookRepository */
        $notebookRepository = $this->getDoctrine()->getRepository(Notebook::class);
        /** @var TaskRepository $taskRepository */
        $taskRepository = $this->getDoctrine()->getRepository(Task::class);
        /** @var User $user */
        $user = $this->getUser();

        // Load notebook first
        /** @var Notebook $notebook */
        $notebook = $notebookRepository->findOneByCode($notebookCode);

        // Validate ownership
        if ($notebook->getUser()->getUserId() !== $user->getUserId()) {
            throw new AccessException('Access Denied for this notebook.');
        }

        /** @var Task[] $tasks */
        $tasks = $taskRepository->findByNotebook($notebook);

        return $this->render('home.html.twig', [
            'tasks'     => $tasks,
            'notebook'  => $notebook,
        ]);
    }
}
