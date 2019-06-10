<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use App\Util\Week;

use App\Entity\User;

use App\Repository\NotebookRepository;
use App\Entity\Notebook;

use App\Repository\TaskRepository;
use App\Entity\Task;

use App\Repository\ShareMapRepository;
use App\Entity\ShareMap;

class AppController extends AbstractController
{
    /**
     * Display the main screen
     * @param string $weekCode
     * @return Response
     */
    public function home(string $weekCode): Response
    {
        // TODO: Default timezone, this should be later set on the User's preferences
        $tz = new \DateTimeZone('America/Monterrey');

        if ($weekCode) {
            if (!Week::validateWeekCode($weekCode)) {
                throw new BadRequestHttpException('Malformed WeekCode');
            }
            $week = Week::newFromWeekCode($weekCode);
        } else {
            // Default to today's week
            $now = new \DateTime('now', $tz);
            $week = Week::newFromDateTime($now);
        }

        // Build today's current week
        $now = new \DateTime('now', $tz);
        $currentWeek = Week::newFromDateTime($now);

        /** @var NotebookRepository $notebookRepository */
        $notebookRepository = $this->getDoctrine()->getRepository(Notebook::class);

        /** @var TaskRepository $taskRepository */
        $taskRepository = $this->getDoctrine()->getRepository(Task::class);

        /** @var User $user */
        $user = $this->getUser();

        // Load notebook first
        /** @var Notebook[] $notebooks */
        $notebooks = $notebookRepository->findByUser($user);

        /** @var Task[] $tasks */
        $tasks = $taskRepository->findAllByUserAndWeek($user, $week);

        // Load pending share requests for the user
        /** @var ShareMapRepository $shareMapRepository */
        $shareMapRepository = $this->getDoctrine()->getRepository(ShareMap::class);

        /** @var ShareMap[] $pendingRequests */
        $pendingRequests = $shareMapRepository->findByInviteEmail($user->getEmail());

        return $this->render('home.html.twig', [
            'notebooks' => $notebooks,
            'tasks'     => $tasks,
            'week'      => $week,
            'currentWeek' => $currentWeek,
            'pendingRequests' => $pendingRequests,
        ]);
    }
}
