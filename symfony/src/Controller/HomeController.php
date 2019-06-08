<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class HomeController extends AbstractController
{
    /**
     * Display the main screen
     * @return Response
     */
    public function home(): Response
    {
        /*
        $user = $this->getUser();

        if (!$user) {
            throw new AccessDeniedException('This action requires an authenticated user');
        }

        */

        // TODO: Check User Admin permission

        return $this->render('home.html.twig', [

        ]);
    }
}
