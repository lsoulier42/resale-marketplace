<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Seller;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * Intégration Stripe Connect (marketplace).
 *
 * - Comptes Connect standard par vendeur·se : onboarding via Account Link
 *   (KYC hébergé par Stripe).
 * - Checkout Session hébergée avec split automatique : le vendeur·se reçoit
 *   le montant moins la commission de la plateforme (application_fee_amount).
 * - Webhooks : paiement → statut « paid », statut du compte, remboursements.
 *
 * Le service est inerte quand STRIPE_SECRET_KEY n'est pas configuré
 * (développement / tests) : isConfigured() retourne alors false et les
 * méthodes de création de session renvoient null.
 */
class StripeService
{
    private ?StripeClient $client = null;

    public function __construct(
        private readonly string $stripeSecretKey,
        private readonly string $stripeWebhookSecret,
        private readonly string $appBaseUrl,
        private readonly float $platformFeePercent,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->stripeSecretKey !== '';
    }

    private function client(): StripeClient
    {
        if ($this->client === null) {
            $this->client = new StripeClient($this->stripeSecretKey);
        }

        return $this->client;
    }

    /**
     * Retourne le compte Connect du vendeur·se, en le créant s'il n'existe pas.
     */
    public function getOrCreateAccount(Seller $seller): string
    {
        if ($seller->getStripeAccountId() !== null) {
            return $seller->getStripeAccountId();
        }

        $account = $this->client()->accounts->create([
            'type' => 'standard',
            'email' => $seller->getUser()->getEmail(),
            'metadata' => [
                'seller_uuid' => (string) $seller->getUuid(),
            ],
        ]);

        $seller->setStripeAccountId($account->id);

        return $account->id;
    }

    /**
     * URL d'onboarding Stripe (Account Link) pour le vendeur·se.
     *
     * @throws ApiErrorException
     */
    public function createAccountLink(Seller $seller): string
    {
        $accountId = $this->getOrCreateAccount($seller);

        $link = $this->client()->accountLinks->create([
            'account' => $accountId,
            'refresh_url' => $this->appBaseUrl . '/profile?stripe=refresh',
            'return_url' => $this->appBaseUrl . '/profile?stripe=return',
            'type' => 'account_onboarding',
        ]);

        return $link->url;
    }

    /**
     * Crée la Checkout Session de la commande (paiement hébergé par Stripe,
     * split automatique vers le compte Connect du vendeur·se).
     *
     * @throws ApiErrorException
     */
    public function createCheckoutSession(Order $order): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $seller = $order->getSeller();
        $accountId = $this->getOrCreateAccount($seller);

        $totalCents = (int) round(((float) $order->getTotalPrice()) * 100);
        $feeCents = (int) round($totalCents * $this->platformFeePercent / 100);

        /** @var Session $session */
        $session = $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $order->getCustomer()->getUser()->getEmail(),
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $totalCents,
                    'product_data' => [
                        'name' => $order->getItem()->getTitle(),
                    ],
                ],
            ]],
            'payment_intent_data' => [
                'transfer_data' => [
                    'destination' => $accountId,
                ],
                'application_fee_amount' => $feeCents,
            ],
            'metadata' => [
                'order_reference' => $order->getReference(),
            ],
            'success_url' => $this->appBaseUrl . '/orders/' . $order->getUuid() . '?stripe=success',
            'cancel_url' => $this->appBaseUrl . '/orders/' . $order->getUuid() . '?stripe=cancel',
        ]);

        $order->setStripeCheckoutSessionId($session->id);

        return $session->url;
    }

    /**
     * Vérifie la signature du webhook et retourne l'événement Stripe.
     *
     * @throws \Stripe\Exception\SignatureVerificationException
     * @throws \RuntimeException
     */
    public function constructWebhookEvent(string $payload, string $signatureHeader): object
    {
        if ($this->stripeWebhookSecret === '') {
            throw new \RuntimeException('STRIPE_WEBHOOK_SECRET n\'est pas configuré.');
        }

        return \Stripe\Webhook::constructEvent($payload, $signatureHeader, $this->stripeWebhookSecret);
    }
}
