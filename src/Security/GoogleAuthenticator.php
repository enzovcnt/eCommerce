<?php

// src/Security/GoogleAuthenticator.php
namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private GoogleClient $client,
        private EntityManagerInterface $em,
        private RouterInterface $router
    ) {}

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $accessToken = $this->fetchAccessToken($this->client);
        $googleUser = $this->client->fetchUserFromToken($accessToken);
        /** @var GoogleUser $googleUser */

        $email = $googleUser->getEmail();

        return new SelfValidatingPassport(
            new UserBadge($email, function() use ($email, $googleUser) {
                $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);
                    $user->setPassword('');
                    $user->setRoles(['ROLE_USER']);
                    $user->setIsVerified(true);
                }

                $this->em->persist($user);
                $this->em->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_shopping'));
    }

    public function onAuthenticationFailure(Request $request, \Throwable $exception): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }
}

