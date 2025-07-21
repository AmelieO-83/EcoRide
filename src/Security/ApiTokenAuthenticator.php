<?php
// src/Security/ApiTokenAuthenticator.php
namespace App\Security;

use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private UtilisateurRepository $userRepo
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->headers->get('X-AUTH-TOKEN');

        if (!$token) {
            throw new CustomUserMessageAuthenticationException('Aucun token fourni');
        }

        return new SelfValidatingPassport(new UserBadge($token, function($token) {
            $user = $this->userRepo->findOneBy(['apiToken' => $token]);

            if (!$user) {
                throw new CustomUserMessageAuthenticationException('Token invalide');
            }

            return $user;
        }));
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Laisse passer la requête si authentification OK
        return null;
    }

    public function onAuthenticationFailure(Request $request, \Symfony\Component\Security\Core\Exception\AuthenticationException $exception): ?Response
    {
        // Répond en JSON en cas d’échec
        return new JsonResponse(
            ['error' => $exception->getMessageKey()],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
