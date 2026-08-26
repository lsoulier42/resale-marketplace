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
 * - Comptes Connect par vendeur·se créés avec l'API Accounts v2
 *   (configuration « recipient ») : la plateforme facture l'acheteur·se puis
 *   transfère au vendeur·se moins la commission (application_fee_amount).
 *   Les identifiants (KYC) sont collectés par l'onboarding hébergé Stripe
 *   (Account Link v2) — la création du compte n'envoie aucune donnée sensible.
 * - Checkout Session hébergée avec split automatique vers le compte v2.
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
     *
     * Compte créé via l'API Accounts v2 avec la configuration « recipient » : la
     * plateforme est le marchand (fees/losses collector = application) et le
     * vendeur·se reçoit les transferts. Les informations d'identité sont
     * collectées ultérieurement par l'onboarding hébergé, aucune donnée sensible
     * n'est envoyée à la création (identité limitée au pays).
     */
    public function getOrCreateAccount(Seller $seller): string
    {
        if ($seller->getStripeAccountId() !== null) {
            return $seller->getStripeAccountId();
        }

        $account = $this->withAdvisoryNoticesSuppressed(fn (): object => $this->client()->v2->core->accounts->create([
            'contact_email' => $seller->getUser()->getEmail(),
            'display_name' => $seller->getUser()->getEmail(),
            'identity' => ['country' => 'fr'],
            'dashboard' => 'none',
            'configuration' => [
                'recipient' => [
                    'capabilities' => [
                        'stripe_balance' => [
                            'stripe_transfers' => ['requested' => true],
                        ],
                    ],
                ],
            ],
            'defaults' => [
                'currency' => 'eur',
                'locales' => ['fr-FR'],
                'responsibilities' => [
                    'fees_collector' => 'application',
                    'losses_collector' => 'application',
                ],
            ],
            'include' => ['configuration.recipient', 'requirements'],
        ]));

        $seller->setStripeAccountId($account->id);

        return $account->id;
    }

    /**
     * Un compte Connect est prêt quand il n'a plus d'exigences en cours ou en
     * retard. Fonctionne pour les comptes v1 comme pour les comptes v2 :
     * l'événement account.updated et le retrieve renvoient un instantané v1 du
     * compte (charges_enabled/details_submitted sont vrais pour un compte
     * « recipient » pleinement onboardé).
     */
    public function isAccountReady(object $account): bool
    {
        $requirements = $account->requirements ?? null;
        if ($requirements !== null) {
            $due = array_merge(
                (array) ($requirements->currently_due ?? []),
                (array) ($requirements->past_due ?? []),
            );

            return $due === [];
        }

        return (bool) ($account->charges_enabled ?? false)
            && (bool) ($account->details_submitted ?? false);
    }

    /**
     * Resynchronise l'état du compte Connect du vendeur·se en interrogeant Stripe
     * (utile au retour de l'onboarding hébergé, sans dépendre du webhook).
     */
    public function syncAccountStatus(Seller $seller): bool
    {
        $accountId = $seller->getStripeAccountId();
        if ($accountId === null || !$this->isConfigured()) {
            return $seller->isStripeAccountReady();
        }

        try {
            $account = $this->withAdvisoryNoticesSuppressed(
                fn (): object => $this->client()->accounts->retrieve($accountId)
            );
        } catch (ApiErrorException) {
            return $seller->isStripeAccountReady();
        }

        $ready = $this->isAccountReady($account);
        $seller->setStripeAccountReady($ready);

        return $ready;
    }

    /**
     * Exécute un appel Stripe en masquant les user warnings purement informatifs
     * (en-tête « Stripe-Notice ») émis par le SDK Stripe, que Symfony escalade en
     * exceptions en environnement de développement bien que l'appel réussisse.
     *
     * @template T
     * @param callable(): T $callable
     * @return T
     */
    private function withAdvisoryNoticesSuppressed(callable $callable): mixed
    {
        set_error_handler(static function (int $level, string $message, string $file, int $line): bool {
            // Seuls les E_USER_WARNING émis depuis le SDK Stripe (fichiers vendor) sont
            // neutralisés : ils correspondent à des en-têtes « Stripe-Notice » purement
            // informatifs (l'opération Stripe a déjà abouti). Tout le reste passe au
            // handler précédent (Debug de Symfony en dev).
            if ($level === \E_USER_WARNING && str_contains($file, 'stripe')) {
                return true;
            }

            return false;
        });

        try {
            return $callable();
        } finally {
            \restore_error_handler();
        }
    }

    /**
     * URL d'onboarding Stripe (Account Link) pour le vendeur·se.
     *
     * @throws ApiErrorException
     */
    public function createAccountLink(Seller $seller): string
    {
        $accountId = $this->getOrCreateAccount($seller);

        $link = $this->withAdvisoryNoticesSuppressed(fn (): object => $this->client()->v2->core->accountLinks->create([
            'account' => $accountId,
            'use_case' => [
                'type' => 'account_onboarding',
                'account_onboarding' => [
                    'configurations' => ['recipient'],
                    'refresh_url' => $this->appBaseUrl . '/profile?stripe=refresh',
                    'return_url' => $this->appBaseUrl . '/profile?stripe=return',
                ],
            ],
        ]));

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
        $session = $this->withAdvisoryNoticesSuppressed(fn (): object => $this->client()->checkout->sessions->create([
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
        ]));

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
