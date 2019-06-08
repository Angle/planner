<?php

namespace App\Controller;

use App\Utility\WeekCode;
use http\Exception\BadUrlException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class AppController extends AbstractController
{
    /**
     * Display the main screen
     * @return Response
     */
    public function home(string $weekCode): Response
    {
        // TODO: Default timezone, this should be later set on the User's preferences
        $tz = new \DateTimeZone('America/Monterrey');

        if (!$weekCode) {
            if (!WeekCode::validateWeekCode($weekCode)) {
                throw new BadUrlException('Malformed WeekCode');
            }
        } else {
            // Default to today's week
            $now = new \DateTime('now', $tz);
        }

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
            throw new AuthenticationException('Access Denied for this notebook.');
        }

        /** @var Task[] $tasks */
        $tasks = $taskRepository->findByNotebook($notebook);

        return $this->render('home.html.twig', [
            'tasks'     => $tasks,
            'notebook'  => $notebook,
        ]);
    }
}
