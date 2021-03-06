<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;

use App\Util\Week;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\Exception\AccessException;

use App\Entity\Notebook;
use App\Entity\Task;
use App\Entity\User;

use App\Form\TaskType;

use App\Preset\StatusCode;
use App\Repository\NotebookRepository;
use App\Repository\TaskRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TaskController extends AbstractController
{
    // TODO: Get user's timezone
    const DEFAULT_TIMEZONE = 'America/Monterrey';

    /**
     * @deprecated
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
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $task = new Task();

        $form = $this->createForm(TaskType::class, $task, [
            'action'    => $this->generateUrl('app_task_new'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($task);

            try {
                $entityManager->flush();
            }catch (\Exception $e) {
                // TODO: WHAT TO DO ON ERROR
                $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_DATABASE_INSERT);
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('task/new.html.twig', [
            'form'  => $form->createView()
        ]);
    }

    /**
     * @param string $taskCode
     * @throws EntityNotFoundException
     * @return Response
     */
    public function view(string $taskCode): Response
    {
        /** @var TaskRepository $taskRespository */
        $taskRespository = $this->getDoctrine()->getRepository(Task::class);

        /** @var Task $task */
        $task = $taskRespository->findOneByCode($taskCode);

        if (!$task) {
            throw new EntityNotFoundException('Task code not found');
        }

        /** @var User $user */
        $user = $this->getUser();

        $notebook = $task->getNotebook();

        if (!$notebook->hasPermission($user)) {
            throw new AccessDeniedException('Notebook is not shared with user');
        }

        return $this->render('task/view.html.twig', [
            'task' => $task,
        ]);
    }

    /**
     * @param Request $request
     * @param string $taskCode
     * @throws EntityNotFoundException
     * @return Response
     */
    public function reopen(Request $request, string $taskCode): Response
    {
        /** @var TaskRepository $taskRespository */
        $taskRespository = $this->getDoctrine()->getRepository(Task::class);

        /** @var Task $task */
        $task = $taskRespository->findOneByCode($taskCode);

        if (!$task) {
            throw new EntityNotFoundException('Task code not found');
        }

        /** @var User $user */
        $user = $this->getUser();

        $notebook = $task->getNotebook();

        if (!$notebook->hasPermission($user)) {
            throw new AccessDeniedException('Notebook is not shared with user');
        }


        // MODIFY THE TASK
        // to RE-OPEN, we'll simply unset any cancel or close times
        $task->setCancelWeekNumber(null);
        $task->setCancelYearNumber(null);

        $task->setCloseWeekNumber(null);
        $task->setCloseYearNumber(null);

        $em = $this->getDoctrine()->getManager();

        try {
            $em->flush();
        }catch (\Exception $e) {
            // TODO: WHAT TO DO ON ERROR
            $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_DATABASE_INSERT);
        }

        // Attempt to return to the refererer
        $referer = $request->headers->get('referer');

        if ($referer) {
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    /**
     * @param Request $request
     * @param string $taskCode
     * @throws EntityNotFoundException
     * @return Response
     */
    public function close(Request $request, string $taskCode): Response
    {
        /** @var TaskRepository $taskRespository */
        $taskRespository = $this->getDoctrine()->getRepository(Task::class);

        /** @var Task $task */
        $task = $taskRespository->findOneByCode($taskCode);

        if (!$task) {
            throw new EntityNotFoundException('Task code not found');
        }

        /** @var User $user */
        $user = $this->getUser();

        $notebook = $task->getNotebook();

        if (!$notebook->hasPermission($user)) {
            throw new AccessDeniedException('Notebook is not shared with user');
        }


        // MODIFY THE TASK
        // to CLOSE, we'll set the current time as the Close Time
        $tz = new \DateTimeZone(self::DEFAULT_TIMEZONE);
        $now = new \DateTime('now', $tz);
        $week = Week::newFromDateTime($now);

        $task->setCloseWeekNumber($week->getWeek());
        $task->setCloseYearNumber($week->getYear());

        // we'll also unset any cancel time (if set)
        $task->setCancelWeekNumber(null);
        $task->setCancelYearNumber(null);

        $em = $this->getDoctrine()->getManager();

        try {
            $em->flush();
        }catch (\Exception $e) {
            // TODO: WHAT TO DO ON ERROR
            $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_DATABASE_INSERT);
        }

        // Attempt to return to the refererer
        $referer = $request->headers->get('referer');

        if ($referer) {
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    /**
     * @param Request $request
     * @param string $taskCode
     * @throws EntityNotFoundException
     * @return Response
     */
    public function cancel(Request $request, string $taskCode): Response
    {
        /** @var TaskRepository $taskRespository */
        $taskRespository = $this->getDoctrine()->getRepository(Task::class);

        /** @var Task $task */
        $task = $taskRespository->findOneByCode($taskCode);

        if (!$task) {
            throw new EntityNotFoundException('Task code not found');
        }

        /** @var User $user */
        $user = $this->getUser();

        $notebook = $task->getNotebook();

        if (!$notebook->hasPermission($user)) {
            throw new AccessDeniedException('Notebook is not shared with user');
        }


        // MODIFY THE TASK
        // to CANCEL, we'll set the current time as the Cancel Time
        $tz = new \DateTimeZone(self::DEFAULT_TIMEZONE);
        $now = new \DateTime('now', $tz);
        $week = Week::newFromDateTime($now);

        $task->setCancelWeekNumber($week->getWeek());
        $task->setCancelYearNumber($week->getYear());

        // we'll also unset any close time (if set)
        $task->setCloseWeekNumber(null);
        $task->setCloseYearNumber(null);


        $em = $this->getDoctrine()->getManager();

        try {
            $em->flush();
        }catch (\Exception $e) {
            // TODO: WHAT TO DO ON ERROR
            $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_DATABASE_INSERT);
        }

        // Attempt to return to the refererer
        $referer = $request->headers->get('referer');

        if ($referer) {
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    /**
     * @param Request $request
     * @param string $taskCode
     * @throws EntityNotFoundException
     * @return Response
     */
    public function delete(Request $request, string $taskCode): Response
    {
        /** @var TaskRepository $taskRespository */
        $taskRespository = $this->getDoctrine()->getRepository(Task::class);

        /** @var Task $task */
        $task = $taskRespository->findOneByCode($taskCode);

        if (!$task) {
            throw new EntityNotFoundException('Task code not found');
        }

        /** @var User $user */
        $user = $this->getUser();

        $notebook = $task->getNotebook();

        if (!$notebook->hasPermission($user)) {
            throw new AccessDeniedException('Notebook is not shared with user');
        }

        // TODO: Check if it has any ActionLogs linked to it

        // DELETE THE TASK
        $em = $this->getDoctrine()->getManager();

        $em->remove($task);

        try {
            $em->flush();
        }catch (\Exception $e) {
            // TODO: WHAT TO DO ON ERROR
            $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_DATABASE_INSERT);
        }

        // Attempt to return to the refererer
        $referer = $request->headers->get('referer');

        if ($referer) {
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    /**
     * @param Request $request
     * @param string $taskCode
     * @throws EntityNotFoundException
     * @return Response
     */
    public function focusSet(Request $request, string $taskCode): Response
    {
        /** @var TaskRepository $taskRespository */
        $taskRespository = $this->getDoctrine()->getRepository(Task::class);

        /** @var Task $task */
        $task = $taskRespository->findOneByCode($taskCode);

        if (!$task) {
            throw new EntityNotFoundException('Task code not found');
        }

        /** @var User $user */
        $user = $this->getUser();

        $notebook = $task->getNotebook();

        if (!$notebook->hasPermission($user)) {
            throw new AccessDeniedException('Notebook is not shared with user');
        }

        // TODO: Check if it has any ActionLogs linked to it

        // SET THE FOCUS TO TODAY
        $tz = new DateTimeZone(self::DEFAULT_TIMEZONE);
        $focusDate = new DateTime('now', $tz);

        $task->setFocusDate($focusDate);

        $em = $this->getDoctrine()->getManager();

        try {
            $em->flush();
        } catch (\Exception $e) {
            // TODO: WHAT TO DO ON ERROR
            $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_DATABASE_INSERT);
        }

        // Attempt to return to the refererer
        $referer = $request->headers->get('referer');

        if ($referer) {
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    /**
     * @param Request $request
     * @param string $taskCode
     * @throws EntityNotFoundException
     * @return Response
     */
    public function focusUnset(Request $request, string $taskCode): Response
    {
        /** @var TaskRepository $taskRespository */
        $taskRespository = $this->getDoctrine()->getRepository(Task::class);

        /** @var Task $task */
        $task = $taskRespository->findOneByCode($taskCode);

        if (!$task) {
            throw new EntityNotFoundException('Task code not found');
        }

        /** @var User $user */
        $user = $this->getUser();

        $notebook = $task->getNotebook();

        if (!$notebook->hasPermission($user)) {
            throw new AccessDeniedException('Notebook is not shared with user');
        }

        // TODO: Check if it has any ActionLogs linked to it

        // UNSET THE FOCUS DATE
        $task->setFocusDate(null);

        $em = $this->getDoctrine()->getManager();

        try {
            $em->flush();
        } catch (\Exception $e) {
            // TODO: WHAT TO DO ON ERROR
            $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_DATABASE_INSERT);
        }

        // Attempt to return to the refererer
        $referer = $request->headers->get('referer');

        if ($referer) {
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
}
