<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Réponse 401 JSON avec un message lisible pour la SPA (sinon l'entry
 * point renvoie « Authentification requise. » générique — le front ne
 * saurait pas distinguer un mauvais mot de passe d'un compte non vérifié).
 */
class JsonLoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new JsonResponse(
            ['error' => ['message' => $this->message($exception)]],
            Response::HTTP_UNAUTHORIZED
        );
    }

    private function message(AuthenticationException $exception): string
    {
        return match (true) {
            $exception instanceof CustomUserMessageAccountStatusException => $exception->getMessageKey(),
            $exception instanceof BadCredentialsException => 'Identifiants incorrects.',
            default => $exception->getMessageKey(),
        };
    }
}
