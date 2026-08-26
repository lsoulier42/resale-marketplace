<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Protège les mutations de l'API JSON (/api/*) contre le CSRF.
 *
 * Le front récupère le jeton via GET /api/csrf-token puis l'envoie
 * dans le header « X-CSRF-Token » sur chaque POST/PUT/PATCH/DELETE —
 * y compris POST /api/login (le factory json_login n'expose pas
 * d'option enable_csrf, la validation se fait donc ici).
 *
 * Priority 16 : s'exécute avant le listener du firewall (priority 8)
 * pour bloquer les requêtes sans jeton avant toute authentification.
 */
class ApiCsrfSubscriber implements EventSubscriberInterface
{
    private const MUTATING_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function __construct(private readonly CsrfTokenManagerInterface $csrfTokenManager)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }
        if (!in_array($request->getMethod(), self::MUTATING_METHODS, true)) {
            return;
        }
        // Les webhooks Stripe sont authentifiés par signature, pas par session.
        if ($request->getPathInfo() === '/api/webhooks/stripe') {
            return;
        }

        $token = (string) $request->headers->get('X-CSRF-Token', '');
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('api', $token))) {
            $event->setResponse(new JsonResponse(
                ['error' => ['message' => 'Jeton CSRF invalide.']],
                Response::HTTP_FORBIDDEN
            ));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 16]];
    }
}
