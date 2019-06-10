<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

use App\Entity\User;

use App\Form\UserRegistrationType;
use App\Form\UserPasswordChangeType;

use App\Preset\StatusCode;

use App\Security\LoginFormAuthenticator;

class SecurityController extends AbstractController
{
    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder,
                             LoginFormAuthenticator $authenticator, GuardAuthenticatorHandler $guardHandler): Response
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();

        $user = new User();

        $form = $this->createForm(UserRegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // TODO: Validate against re-type password
            $plainTextPassword = $form->get('checkPassword')->getData();

            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));

            $em->persist($user);

            try {
                $em->flush();
            }catch (\Exception $e) {
                $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_DATABASE_INSERT);
            }

            // after validating the user and saving them to the database
            // authenticate the user and use onAuthenticationSuccess on the authenticator
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,          // the User object you just created
                $request,
                $authenticator, // authenticator whose onAuthenticationSuccess you want to use
                'main'          // the name of your firewall in security.yaml
            );
        }


        return $this->render('security/register.html.twig', [
            'form'  => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function changePassword(Request $request,UserPasswordEncoderInterface $passwordEncoder): Response
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserPasswordChangeType::class, null, [
            'action'    => $this->generateUrl('app_change_password'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $error = false;

            $oldPassword = $form->get('oldPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            $checkPassword = $form->get('checkPassword')->getData();

            if(!$passwordEncoder->isPasswordValid($user, $oldPassword)) {
                $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_OLD_PASSWORD_NO_MATCH);
                $error = true;
            }

            if ($newPassword !== $checkPassword) {
                $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_PASSWORD_NO_MATCH);
                $error = true;
            }

            if (!$error) {
                $newEncodedPassword = $passwordEncoder->encodePassword(
                    $user,
                    $newPassword
                );
                $user->setPassword($newEncodedPassword);
            }

            try {
                $em->flush();
            }catch (\Exception $e) {
                $error = true;
                $this->addFlash(StatusCode::LABEL_ERROR, StatusCode::ERROR_DATABASE_INSERT);
            }

            if (!$error) $this->addFlash(StatusCode::LABEL_SUCCESS, StatusCode::SUCCESS_OK);

            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/change-password.html.twig', [
            'form'  => $form->createView()
        ]);
    }
}
