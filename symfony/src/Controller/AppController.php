<?php

namespace App\Controller;

use DateTime;

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
     * @param Request $request
     * @param string $weekCode
     * @return Response
     */
    public function home(Request $request, string $weekCode): Response
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
            $now = new DateTime('now', $tz);
            $week = Week::newFromDateTime($now);
        }

        // Build today's current week
        $now = new DateTime('now', $tz);
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
        $tasks = $taskRepository->findAllInNotebooksByWeek($notebooks, $week);

        // check if we have the special query parameter to show only open tasks
        if ($request->query->has('only-open')) {
            foreach ($tasks as $k => $t) {
                if ($t->getStatus() != Task::STATUS_OPEN) {
                    // if the task is not in status open, remove it from the list.
                    unset($tasks[$k]);
                }
            }
        }

        // Load pending share requests for the user
        /** @var ShareMapRepository $shareMapRepository */
        $shareMapRepository = $this->getDoctrine()->getRepository(ShareMap::class);

        /** @var ShareMap[] $pendingRequests */
        $pendingRequests = $shareMapRepository->findByInviteEmail($user->getEmail());

        return $this->render('home.html.twig', [
            'notebooks'         => $notebooks,
            'tasks'             => $tasks,
            'week'              => $week,
            'currentWeek'       => $currentWeek,
            'pendingRequests'   => $pendingRequests,
        ]);
    }


    /**
     * Display the main screen
     * @param Request $request
     * @param string|null $focusDate
     * @return Response
     */
    public function focus(Request $request, string $focusDate): Response
    {
        // TODO: Default timezone, this should be later set on the User's preferences
        $tz = new \DateTimeZone('America/Monterrey');

        // Default to today's week
        $now = new DateTime('now', $tz);
        $week = Week::newFromDateTime($now);
        $currentWeek = clone $week;

        $focus = DateTime::createFromFormat('!Y-m-d', $focusDate, $tz);

        if (!$focus) {
            throw new BadRequestHttpException('Malformed Focus Date');
        }

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
        $tasks = $taskRepository->findAllInNotebooksByFocusDate($notebooks, $focus);

        return $this->render('focus.html.twig', [
            'notebooks'     => $notebooks,
            'tasks'         => $tasks,
            'focus'         => $focus,
            'week'          => $week,
            'currentWeek'   => $currentWeek,
        ]);
    }
}
