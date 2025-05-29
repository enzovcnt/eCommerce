<?php

namespace App\Controller;


use App\Entity\Profile;
use App\Entity\User;
use App\Form\RegistrationForm;
use App\Repository\UserRepository;
use App\Service\JWTService;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,
    JWTService $jwt, SendEmailService $mail
    ): Response
    {
        $user = new User();
        $profile = new Profile();



        $user->setProfile($profile);
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->persist($profile);
            $entityManager->flush();

            // do anything else you need here, like send an email

            //générer le token
            //header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];
            //payload
            $payload = [
                'user_id' => $user->getId(),
            ];

            //générer le token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));



            //envoyer l'email
            $mail->send(
                'mail@enzovincent.org',
                $user->getEmail(),
                'Your account has been created.',
                'register',
                compact('user', 'token') //['user' => $user, 'token'=> $token]
            );

        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verif/{token}', name: 'verif_user')]
    public function verifUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $entityManager,): Response
    {
        if($jwt->isValid($token) && !$jwt->isExpired($token) &&
            $jwt->check($token, $this->getParameter('app.jwtsecret')))
        {
            //le token est valide
            //on récupére les données du payload
            $payload = $jwt->getPayload($token);



            //on récupère le user
            $user = $userRepository->find($payload['user_id']);

            //on vérifie qu'on a bien un user et qu'il n'est pas déjà activé
            if ($user && !$user->isVerified())
            {
                $user->setIsVerified(true);
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte est activé');
                return $this->redirectToRoute('app_login');
            }

        }

        $this->addFlash('danger', 'token invalide ou expiré');
        return $this->redirectToRoute('app_register');
    }
}
