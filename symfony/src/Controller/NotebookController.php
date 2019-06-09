<?php

namespace App\Controller;

use App\Entity\Notebook;
use App\Entity\User;

use App\Form\NotebookType;

use App\Repository\NotebookRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            }

            // TODO WHAT TO DO ON SUCCESS
        }

        return $this->render('notebook/new.html.twig', [
            'form'  => $form->createView()
        ]);
    }

    /**
     * @return Response
     */
    public function view(): Response
    {
        return $this->render('notebook/view.html.twig', [
            // none.
        ]);
    }
}
