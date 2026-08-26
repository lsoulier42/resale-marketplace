<?php

namespace App\Tests\Api;

use App\Entity\Seller;
use Stripe\WebhookSignature;

/**
 * Tests fonctionnels des webhooks Stripe (signature vérifiée, sans appel
 * réseau : les événements sont construits localement avec la clé de test
 * définie dans phpunit.xml.dist).
 */
class StripeWebhookApiTest extends ApiTestCase
{
    private const WEBHOOK_SECRET = 'whsec_test';

    public function testWebhookRejectsMissingSignature(): void
    {
        $this->client->request('POST', '/api/webhooks/stripe', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ], '{"id":"evt_1","object":"event","type":"checkout.session.completed","data":{"object":{}}}');

        self::assertResponseStatusCodeSame(400);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Signature de webhook invalide.', $data['error']['message']);
    }

    public function testCheckoutCompletedMarksOrderPaid(): void
    {
        $this->login('camille@example.test');
        $item = $this->jsonGet('/api/items?limit=1')['items'][0];
        $order = $this->jsonMutation('POST', '/api/orders', [
            'itemUuid' => $item['uuid'],
        ], 201)['order'];
        self::assertSame('pending_payment', $order['status']);

        $this->postWebhook([
            'id' => 'evt_cs_completed',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'object' => 'checkout.session',
                    'metadata' => ['order_reference' => $order['reference']],
                    'payment_intent' => 'pi_test_123',
                ],
            ],
        ]);

        $updated = $this->jsonGet('/api/orders/' . $order['uuid'])['order'];
        self::assertSame('paid', $updated['status']);
        self::assertFalse($updated['canPay'], 'Une commande payée n\'attend plus de paiement.');
    }

    public function testAccountUpdatedMarksSellerReady(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $seller = $entityManager
            ->getRepository(Seller::class)
            ->findOneBy(['user' => $this->userEntity('jordan@example.test')]);
        self::assertNotNull($seller);
        $seller->setStripeAccountId('acct_test_123');
        $seller->setStripeAccountReady(false);
        $entityManager->flush();

        $this->postWebhook([
            'id' => 'evt_acct_updated',
            'object' => 'event',
            'type' => 'account.updated',
            'data' => [
                'object' => [
                    'id' => 'acct_test_123',
                    'object' => 'account',
                    'charges_enabled' => true,
                    'details_submitted' => true,
                ],
            ],
        ]);

        $entityManager->clear();
        $seller = $entityManager->getRepository(Seller::class)->findOneBy(['stripeAccountId' => 'acct_test_123']);
        self::assertNotNull($seller);
        self::assertTrue($seller->isStripeAccountReady());
    }

    public function testAccountUpdatedWithPendingRequirementsIsNotReady(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $seller = $entityManager
            ->getRepository(Seller::class)
            ->findOneBy(['user' => $this->userEntity('jordan@example.test')]);
        self::assertNotNull($seller);
        $seller->setStripeAccountId('acct_v2_pending');
        $seller->setStripeAccountReady(true);
        $entityManager->flush();

        // Compte v2 « recipient » pas encore onboardé : des exigences restent
        // dues (instantané v1) même si charges_enabled est vrai.
        $this->postWebhook([
            'id' => 'evt_acct_pending',
            'object' => 'event',
            'type' => 'account.updated',
            'data' => [
                'object' => [
                    'id' => 'acct_v2_pending',
                    'object' => 'account',
                    'charges_enabled' => true,
                    'details_submitted' => true,
                    'requirements' => [
                        'currently_due' => ['external_account'],
                        'past_due' => [],
                    ],
                ],
            ],
        ]);

        $entityManager->clear();
        $seller = $entityManager->getRepository(Seller::class)->findOneBy(['stripeAccountId' => 'acct_v2_pending']);
        self::assertNotNull($seller);
        self::assertFalse($seller->isStripeAccountReady());
    }

    public function testAccountUpdatedV2RequirementsClearMarksSellerReady(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $seller = $entityManager
            ->getRepository(Seller::class)
            ->findOneBy(['user' => $this->userEntity('sam@example.test')]);
        self::assertNotNull($seller);
        $seller->setStripeAccountId('acct_v2_ready');
        $seller->setStripeAccountReady(false);
        $entityManager->flush();

        // Compte v2 « recipient » pleinement onboardé : plus aucune exigence
        // en cours ni en retard (charges_enabled peut rester vrai ici).
        $this->postWebhook([
            'id' => 'evt_acct_v2_ready',
            'object' => 'event',
            'type' => 'account.updated',
            'data' => [
                'object' => [
                    'id' => 'acct_v2_ready',
                    'object' => 'account',
                    'requirements' => [
                        'currently_due' => [],
                        'past_due' => [],
                    ],
                ],
            ],
        ]);

        $entityManager->clear();
        $seller = $entityManager->getRepository(Seller::class)->findOneBy(['stripeAccountId' => 'acct_v2_ready']);
        self::assertNotNull($seller);
        self::assertTrue($seller->isStripeAccountReady());
    }

    public function testChargeRefundedCancelsOrder(): void
    {
        $this->login('camille@example.test');
        $item = $this->jsonGet('/api/items?limit=1')['items'][0];
        $order = $this->jsonMutation('POST', '/api/orders', [
            'itemUuid' => $item['uuid'],
        ], 201)['order'];

        // Simule le passage à « payé » puis le remboursement.
        $this->postWebhook([
            'id' => 'evt_cs_completed',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_refund',
                    'object' => 'checkout.session',
                    'metadata' => ['order_reference' => $order['reference']],
                    'payment_intent' => 'pi_test_refund',
                ],
            ],
        ]);

        $this->postWebhook([
            'id' => 'evt_charge_refunded',
            'object' => 'event',
            'type' => 'charge.refunded',
            'data' => [
                'object' => [
                    'id' => 'ch_test_refund',
                    'object' => 'charge',
                    'payment_intent' => 'pi_test_refund',
                ],
            ],
        ]);

        $updated = $this->jsonGet('/api/orders/' . $order['uuid'])['order'];
        self::assertSame('cancelled', $updated['status']);
    }

    /** @param array<string, mixed> $event */
    private function postWebhook(array $event): void
    {
        $payload = json_encode($event, JSON_THROW_ON_ERROR);
        $signature = WebhookSignature::generateSignatureHeader($payload, self::WEBHOOK_SECRET);

        $this->client->request('POST', '/api/webhooks/stripe', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => $signature,
        ], $payload);

        self::assertResponseIsSuccessful();
    }

    private function userEntity(string $email): object
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        return $entityManager->getRepository(\App\Entity\User::class)->findOneBy(['email' => $email]);
    }
}
