<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\SellerRepository;
use App\Service\StripeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Espace de paiement du vendeur·se (Stripe Connect).
 *
 * - GET  /api/me/stripe     → état du compte Connect
 * - POST /api/me/stripe/onboarding → URL d'onboarding (Account Link)
 */
#[IsGranted('ROLE_USER')]
#[Route(path: '/api/me/stripe')]
class StripeApiController extends ApiController
{
    public function __construct(private readonly StripeService $stripeService)
    {
    }

    #[Route(path: '', name: 'api_me_stripe', methods: ['GET'])]
    public function status(
        SellerRepository $sellerRepository,
        #[CurrentUser] User $user
    ): JsonResponse {
        $seller = $sellerRepository->findByUser($user);

        return $this->jsonResponse([
            'stripe' => [
                'isSeller' => $seller !== null,
                'onboarded' => $seller?->getStripeAccountId() !== null,
                'ready' => $seller?->isStripeAccountReady() ?? false,
                'configured' => $this->stripeService->isConfigured(),
            ],
        ]);
    }

    #[Route(path: '/onboarding', name: 'api_me_stripe_onboarding', methods: ['POST'])]
    public function onboarding(
        SellerRepository $sellerRepository,
        #[CurrentUser] User $user
    ): JsonResponse {
        if (!$this->stripeService->isConfigured()) {
            return $this->jsonError('Le paiement en ligne n\'est pas configuré.', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $seller = $sellerRepository->findByUser($user);
        if ($seller === null) {
            return $this->jsonError(
                'Vous devez être vendeur·se pour configurer vos paiements.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $url = $this->stripeService->createAccountLink($seller);
        } catch (\Stripe\Exception\ApiErrorException) {
            return $this->jsonError(
                'Impossible de démarrer l\'onboarding Stripe, réessayez dans quelques minutes.',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        // Le compte Connect peut avoir été créé à cette occasion.
        $sellerRepository->createOrUpdate($seller);

        return $this->jsonResponse(['url' => $url]);
    }
}
