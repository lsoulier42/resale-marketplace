<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Point d'entrée du firewall : JSON 401 pour les appels /api/* non
 * authentifiés (SPA), redirection vers l'accueil de la SPA sinon
 * (les routes non-API sont servies par nginx / index.html).
 */
class ApiAwareAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if (str_starts_with($request->getPathInfo(), '/api/')) {
            return new JsonResponse(
                ['error' => ['message' => 'Authentification requise.']],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return new RedirectResponse('/');
    }
}
