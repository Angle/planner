<?php

namespace App\Controller;

use App\Entity\Notebook;
use App\Entity\Task;
use App\Entity\User;

use App\Repository\NotebookRepository;
use App\Repository\TaskRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\Exception\AccessException;

class TaskController extends AbstractController
{

    /**
     * @param string $notebookCode
     * @return Response
     */
    public function list(string $notebookCode): Response
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

        return $this->render('task/list.html.twig', [
            'tasks'     => $tasks,
            'notebook'  => $notebook,
        ]);
    }

    /**
     * @return Response
     */
    public function new(): Response
    {
        return $this->render('task/new.html.twig', [
            // none.
        ]);
    }

    /**
     * @return Response
     */
    public function view(string $taskCode): Response
    {
        return $this->render('task/view.html.twig', [
            // none.
        ]);
    }
}
