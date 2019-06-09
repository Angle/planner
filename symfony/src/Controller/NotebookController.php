<?php

namespace App\Controller;

use App\Entity\Notebook;
use App\Entity\User;

use App\Repository\NotebookRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @return Response
     */
    public function new(): Response
    {
        return $this->render('notebook/new.html.twig', [
            // none.
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
