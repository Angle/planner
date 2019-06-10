<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Notebook;
use App\Entity\User;

use App\Form\NotebookType;

use App\Repository\NotebookRepository;

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
            }catch (\Exception $e) {
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

        return $this->render('notebook/view.html.twig', [
            'notebook' => $notebook,
        ]);
    }
}
