<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Notebook;
use App\Entity\User;
use App\Entity\ShareMap;

use App\Form\NotebookType;
use App\Form\ShareMapType;

use App\Repository\NotebookRepository;
use App\Repository\ShareMapRepository;

use App\Preset\StatusCode;

class NotebookController extends AbstractController
{
    /**
     * @return Response
     */
    public function list(): Response
    {
        /** @var NotebookRepository $notebookRepository */
        $notebookRepository = $this->getDoctrine()->getRepository(Notebook::class);
        /** @var User $user */
        $user = $this->getUser();

        /** @var Notebook[] $notebooks */
        $notebooks = $notebookRepository->findByUser($user);

        return $this->render('notebook/list.html.twig', [
            'notebooks' => $notebooks,
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
        /** @var User $user */
        $user = $this->getUser();

        $notebook = new Notebook();
        $notebook->setUser($user);

        $form = $this->createForm(NotebookType::class, $notebook, [
            'action' => $this->generateUrl('app_notebook_new'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($notebook);

            try {
                $entityManager->flush();
            } catch (\Exception $e) {
                // TODO WHAT TO DO ON ERROR
                $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_DATABASE_INSERT);
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('notebook/new.html.twig', [
            'form'  => $form->createView()
        ]);
    }

    /**
     * @param string $notebookCode
     * @throws EntityNotFoundException
     * @return Response
     */
    public function view(string $notebookCode): Response
    {
        /** @var EntityManagerInterface $entityManager */
        $em = $this->getDoctrine()->getManager();
        /** @var NotebookRepository $notebookRepository */
        $notebookRepository = $this->getDoctrine()->getRepository(Notebook::class);

        /** @var Notebook $notebook */
        $notebook = $notebookRepository->findOneByCode($notebookCode);

        if (!$notebook) {
            throw new EntityNotFoundException('Notebook code not found');
        }

        /** @var User $user */
        $user = $this->getUser();

        if (!$notebook->hasPermission($user)) {
            throw new AccessDeniedException('Notebook is not shared with user');
        }

        $shareMap = new ShareMap();
        $shareMap->setNotebook($notebook);

        $form = $this->createForm(ShareMapType::class, $shareMap, [
            'action'    => $this->generateUrl('app_notebook_share', ['notebookCode' => $notebook->getCode()]),
            'attr'      => ['class' => 'form'],
        ]);

        return $this->render('notebook/view.html.twig', [
            'notebook'  => $notebook,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param string $notebookCode
     * @param Request $request
     * @throws EntityNotFoundException
     * @return Response
     */
    public function share(string $notebookCode, Request $request): Response
    {
        /** @var EntityManagerInterface $entityManager */
        $em = $this->getDoctrine()->getManager();
        /** @var NotebookRepository $notebookRepository */
        $notebookRepository = $this->getDoctrine()->getRepository(Notebook::class);
        /** @var ShareMapRepository $shareMapRepository */
        $shareMapRepository = $this->getDoctrine()->getRepository(ShareMap::class);

        /** @var Notebook $notebook */
        $notebook = $notebookRepository->findOneByCode($notebookCode);

        if (!$notebook) {
            throw new EntityNotFoundException('Notebook code not found');
        }

        /** @var User $user */
        $user = $this->getUser();

        if (!$notebook->hasPermission($user)) {
            throw new AccessDeniedException('Notebook is not shared with user');
        }

        $shareMap = new ShareMap();
        $shareMap->setNotebook($notebook);

        $form = $this->createForm(ShareMapType::class, $shareMap, [
            'action'    => $this->generateUrl('app_notebook_share', ['notebookCode' => $notebook->getCode()]),
            'attr'      => ['class' => 'form'],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $error = false;

            // Check for a previous ShareMap by inviteEmail
            $maps = $shareMapRepository->findByNotebookAndEmail($notebook, $shareMap->getInviteEmail());

            if (count($maps) > 0) {
                $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_NOTEBOOK_SHARE);
                $error = true;
            }

            if(!$error) {

                $em->persist($shareMap);

                try {
                    $em->flush();
                } catch (\Exception $e) {
                    $error = true;
                    $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_NOTEBOOK_SHARE);
                }

            }
            if (!$error) $this->addFlash(StatusCode::LABEL_SUCCESS, StatusCode::SUCCESS_NOTEBOOK_SHARE);
        }

        return $this->redirectToRoute('app_home');
    }

    public function shareAccept(string $shareMapCode): Response
    {
        /** @var ShareMapRepository $shareMapRepository */
        $shareMapRepository = $this->getDoctrine()->getRepository(ShareMap::class);

        /** @var ShareMap $map */
        $map = $shareMapRepository->findOneByCode($shareMapCode);

        if (!$map) {
            throw new EntityNotFoundException('ShareMap code not found');
        }

        // Check that the current user is the invitee
        /** @var User $user */
        $user = $this->getUser();

        if ($map->getInviteEmail() != $user->getEmail()) {
            throw new AccessDeniedException('ShareMap is not meant for this user');
        }

        // All good, proceed to ACCEPT the invite
        $map->setInviteEmail(null);
        $map->setUser($user);

        $em = $this->getDoctrine()->getManager();

        try {
            $em->flush();
            $this->addFlash(StatusCode::LABEL_SUCCESS, StatusCode::SUCCESS_OK);
        } catch (\Exception $e) {
            $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_UNKNOWN);
        }


        return $this->redirectToRoute('app_home');
    }

    public function shareReject(string $shareMapCode): Response
    {
        /** @var ShareMapRepository $shareMapRepository */
        $shareMapRepository = $this->getDoctrine()->getRepository(ShareMap::class);

        /** @var ShareMap $map */
        $map = $shareMapRepository->findOneByCode($shareMapCode);

        if (!$map) {
            throw new EntityNotFoundException('ShareMap code not found');
        }

        // Check that the current user is the invitee
        /** @var User $user */
        $user = $this->getUser();

        if ($map->getInviteEmail() != $user->getEmail()) {
            throw new AccessDeniedException('ShareMap is not meant for this user');
        }

        // All good, proceed to REJECT the invite
        $em = $this->getDoctrine()->getManager();
        $em->remove($map);

        try {
            $em->flush();
            $this->addFlash(StatusCode::LABEL_SUCCESS, StatusCode::SUCCESS_OK);
        } catch (\Exception $e) {
            $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_UNKNOWN);
        }


        return $this->redirectToRoute('app_home');
    }

    public function shareRemove(string $shareMapCode): Response
    {

        /** @var ShareMapRepository $shareMapRepository */
        $shareMapRepository = $this->getDoctrine()->getRepository(ShareMap::class);

        /** @var ShareMap $map */
        $map = $shareMapRepository->findOneByCode($shareMapCode);

        if (!$map) {
            throw new EntityNotFoundException('ShareMap code not found');
        }

        // Only the Notebook owner can kick/remove a user
        /** @var User $user */
        $user = $this->getUser();
        $notebook = $map->getNotebook();

        if ($notebook->getUser()->getUserId() != $user->getUserId()) {
            throw new AccessDeniedException('ShareMap can only be removed by owner');
        }

        // All good, proceed to REMOVE the share
        $em = $this->getDoctrine()->getManager();
        $em->remove($map);

        try {
            $em->flush();
            $this->addFlash(StatusCode::LABEL_SUCCESS, StatusCode::SUCCESS_OK);
        } catch (\Exception $e) {
            $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_UNKNOWN);
        }


        return $this->redirectToRoute('app_home');

    }
}
