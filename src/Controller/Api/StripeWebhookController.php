<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Entity\Seller;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\Repository\SellerRepository;
use App\Service\StripeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Réception des webhooks Stripe (route publique, signée — exempte de CSRF,
 * voir ApiCsrfSubscriber).
 *
 * - checkout.session.completed → commande payée
 * - account.updated → état du compte Connect du vendeur·se
 * - charge.refunded → commande annulée (remboursement)
 */
#[IsGranted('PUBLIC_ACCESS')]
#[Route(path: '/api/webhooks/stripe', name: 'api_webhooks_stripe', methods: ['POST'])]
class StripeWebhookController extends ApiController
{
    public function __invoke(
        Request $request,
        StripeService $stripe,
        OrderRepository $orderRepository,
        SellerRepository $sellerRepository,
    ): JsonResponse {
        $payload = (string) $request->getContent();
        $signature = (string) $request->headers->get('Stripe-Signature', '');

        try {
            $event = $stripe->constructWebhookEvent($payload, $signature);
        } catch (\Throwable) {
            return $this->jsonError('Signature de webhook invalide.', Response::HTTP_BAD_REQUEST);
        }

        $object = $event->data->object ?? null;
        if ($object !== null) {
            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->onCheckoutCompleted($object, $orderRepository);
                    break;
                case 'account.updated':
                    $this->onAccountUpdated($object, $sellerRepository, $stripe);
                    break;
                case 'charge.refunded':
                    $this->onChargeRefunded($object, $orderRepository);
                    break;
            }
        }

        return $this->jsonResponse(['received' => true]);
    }

    /** @param object $session */
    private function onCheckoutCompleted(object $session, OrderRepository $orderRepository): void
    {
        $order = $this->findOrder($session, $orderRepository);
        if ($order === null || $order->getStatus() !== OrderStatus::PENDING_PAYMENT) {
            return;
        }

        $order->setStatus(OrderStatus::PAID);
        $paymentIntent = $session->payment_intent ?? null;
        if (is_string($paymentIntent)) {
            $order->setStripePaymentIntentId($paymentIntent);
        }
        $orderRepository->createOrUpdate($order);
    }

    /** @param object $account */
    private function onAccountUpdated(object $account, SellerRepository $sellerRepository, StripeService $stripe): void
    {
        if (!isset($account->id) || !is_string($account->id)) {
            return;
        }

        $seller = $sellerRepository->findOneBy(['stripeAccountId' => $account->id]);
        if ($seller === null) {
            return;
        }

        $seller->setStripeAccountReady($stripe->isAccountReady($account));
        $sellerRepository->createOrUpdate($seller);
    }

    /** @param object $charge */
    private function onChargeRefunded(object $charge, OrderRepository $orderRepository): void
    {
        $paymentIntent = $charge->payment_intent ?? null;
        if (!is_string($paymentIntent) || $paymentIntent === '') {
            return;
        }

        $order = $orderRepository->findOneBy(['stripePaymentIntentId' => $paymentIntent]);
        if ($order === null || $order->getStatus() === OrderStatus::CANCELLED) {
            return;
        }

        $order->setStatus(OrderStatus::CANCELLED);
        $orderRepository->createOrUpdate($order);
    }

    /** @param object $session */
    private function findOrder(object $session, OrderRepository $orderRepository): ?Order
    {
        $reference = $session->metadata->order_reference ?? null;
        if (is_string($reference) && $reference !== '') {
            $order = $orderRepository->findOneBy(['reference' => $reference]);
            if ($order !== null) {
                return $order;
            }
        }

        $sessionId = $session->id ?? null;
        if (is_string($sessionId) && $sessionId !== '') {
            return $orderRepository->findOneBy(['stripeCheckoutSessionId' => $sessionId]);
        }

        return null;
    }
}
