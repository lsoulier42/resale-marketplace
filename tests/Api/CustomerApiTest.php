<?php

namespace App\Tests\Api;

/**
 * Tests fonctionnels de l'espace client : commandes, adresses, profil, avis.
 */
class CustomerApiTest extends ApiTestCase
{
    public function testUnauthenticatedRequestGetsJson401(): void
    {
        $this->client->request('GET', '/api/orders', [], [], ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseStatusCodeSame(401);
    }

    public function testCustomerOrdersIndexContainsOwnOrders(): void
    {
        $this->login('camille@example.test');

        $data = $this->jsonGet('/api/orders');

        self::assertNotEmpty($data['orders']);
        foreach ($data['orders'] as $order) {
            self::assertArrayHasKey('reference', $order);
            self::assertArrayHasKey('statusLabel', $order);
            self::assertArrayHasKey('item', $order);
            self::assertArrayHasKey('allowedTransitions', $order);
        }
    }

    public function testOrderCreationDecrementsStockAndReturnsCheckout(): void
    {
        $this->login('camille@example.test');
        $item = $this->jsonGet('/api/items?limit=1')['items'][0];

        $created = $this->jsonMutation('POST', '/api/orders', [
            'itemUuid' => $item['uuid'],
            'shippingFee' => '3.50',
        ], 201);

        $order = $created['order'];
        self::assertSame('pending_payment', $order['status']);
        self::assertSame('En attente de paiement', $order['statusLabel']);
        self::assertSame('3.50', $order['shippingFee']);
        $expectedTotal = number_format((float) $item['price'] + 3.5, 2, '.', '');
        self::assertSame($expectedTotal, $order['totalPrice']);
        self::assertNull($created['checkoutUrl'], 'Stripe non configuré en test : pas de session.');
        self::assertTrue($order['canPay'], 'Le client peut payer sa commande en attente.');
        self::assertFalse($order['canUpdateStatus'], 'Le client ne peut pas changer le statut.');

        // L'article est lié à une commande (stock décrémenté d'une unité).
        $after = $this->jsonGet('/api/items/' . $item['uuid'])['item'];
        self::assertTrue($after['isSold']);
        self::assertSame($item['availableCount'] - 1, $after['availableCount']);
    }

    public function testSoldItemCannotBeOrderedAgain(): void
    {
        $this->login('camille@example.test');
        $item = $this->jsonGet('/api/items?limit=1')['items'][0];
        $this->jsonMutation('POST', '/api/orders', ['itemUuid' => $item['uuid']], 201);

        $this->jsonMutation('POST', '/api/orders', ['itemUuid' => $item['uuid']], 422);
    }

    public function testCustomerCannotUpdateOrderStatus(): void
    {
        $this->login('camille@example.test');
        $order = $this->jsonGet('/api/orders')['orders'][0];

        $this->jsonMutation('PATCH', '/api/orders/' . $order['uuid'] . '/status', ['status' => 'paid'], 403);
    }

    public function testCheckoutEndpointRequiresPendingOwnOrder(): void
    {
        $this->login('camille@example.test');
        $item = $this->jsonGet('/api/items?limit=1')['items'][0];
        $orderUuid = $this->jsonMutation('POST', '/api/orders', [
            'itemUuid' => $item['uuid'],
        ], 201)['order']['uuid'];

        // Stripe non configuré en test : le paiement n'est pas disponible.
        $this->jsonMutation('POST', '/api/orders/' . $orderUuid . '/checkout', null, 503);

        // Un autre client ne peut pas payer la commande.
        $this->login('sophie@example.test');
        $this->jsonMutation('POST', '/api/orders/' . $orderUuid . '/checkout', null, 403);
    }

    public function testStripeOnboardingRequiresSeller(): void
    {
        $this->login('camille@example.test');

        // Stripe non configuré en test : 503 même pour un·e vendeur·se.
        $this->jsonMutation('POST', '/api/me/stripe/onboarding', null, 503);
    }

    public function testSellerFollowsStatusTransitions(): void
    {
        $available = $this->availableItemWithSeller();

        $this->login('camille@example.test');
        $created = $this->jsonMutation('POST', '/api/orders', [
            'itemUuid' => $available['itemUuid'],
        ], 201);
        $orderUuid = $created['order']['uuid'];

        $this->login($available['sellerEmail']);
        $order = $this->jsonGet('/api/orders/' . $orderUuid)['order'];
        self::assertTrue($order['canUpdateStatus']);

        // pending_payment → paid → shipped → delivered
        $this->jsonMutation('PATCH', '/api/orders/' . $orderUuid . '/status', ['status' => 'paid']);
        $this->jsonMutation('PATCH', '/api/orders/' . $orderUuid . '/status', [
            'status' => 'shipped',
            'trackingNumber' => 'TS-42',
            'shippingProvider' => 'Colissimo',
        ]);
        $delivered = $this->jsonMutation('PATCH', '/api/orders/' . $orderUuid . '/status', ['status' => 'delivered']);

        self::assertSame('delivered', $delivered['order']['status']);
        self::assertSame('TS-42', $delivered['order']['trackingNumber']);

        // Transition interdite (delivered → paid).
        $this->jsonMutation('PATCH', '/api/orders/' . $orderUuid . '/status', ['status' => 'paid'], 422);
        // Statut inconnu.
        $this->jsonMutation('PATCH', '/api/orders/' . $orderUuid . '/status', ['status' => 'teleported'], 422);
    }

    public function testReviewFlow(): void
    {
        $available = $this->availableItemWithSeller();

        $this->login('camille@example.test');
        $orderUuid = $this->jsonMutation('POST', '/api/orders', [
            'itemUuid' => $available['itemUuid'],
        ], 201)['order']['uuid'];

        // Pas encore livrée → avis refusé.
        $this->jsonMutation('POST', '/api/reviews', ['orderUuid' => $orderUuid, 'star' => 5], 422);

        $this->login($available['sellerEmail']);
        $this->jsonMutation('PATCH', '/api/orders/' . $orderUuid . '/status', ['status' => 'paid']);
        $this->jsonMutation('PATCH', '/api/orders/' . $orderUuid . '/status', ['status' => 'shipped']);
        $this->jsonMutation('PATCH', '/api/orders/' . $orderUuid . '/status', ['status' => 'delivered']);

        $this->login('camille@example.test');
        $order = $this->jsonGet('/api/orders/' . $orderUuid)['order'];
        self::assertTrue($order['canReview']);

        $review = $this->jsonMutation('POST', '/api/reviews', [
            'orderUuid' => $orderUuid,
            'star' => 5,
            'comment' => 'Parfait !',
        ], 201);
        self::assertSame(5, $review['review']['star']);
        self::assertSame('Parfait !', $review['review']['comment']);

        // Doublon refusé.
        $this->jsonMutation('POST', '/api/reviews', ['orderUuid' => $orderUuid, 'star' => 4], 422);
    }

    public function testAddressCrud(): void
    {
        $this->login('sophie@example.test');

        $created = $this->jsonMutation('POST', '/api/addresses', [
            'name' => 'Sophie L.',
            'addressLine' => '12 rue des Lilas',
            'city' => 'Marseille',
            'zipCode' => '13006',
            'country' => 'France',
        ], 201);
        $uuid = $created['address']['uuid'];
        self::assertSame('12 rue des Lilas', $created['address']['addressLine']);

        $updated = $this->jsonMutation('PUT', '/api/addresses/' . $uuid, [
            'name' => 'Sophie L.',
            'addressLine' => '14 rue des Lilas',
            'city' => 'Marseille',
            'zipCode' => '13006',
            'country' => 'France',
        ]);
        self::assertSame('14 rue des Lilas', $updated['address']['addressLine']);

        $this->jsonMutation('DELETE', '/api/addresses/' . $uuid);

        $addresses = $this->jsonGet('/api/addresses');
        foreach ($addresses['addresses'] as $address) {
            self::assertNotSame($uuid, $address['uuid']);
        }
    }

    public function testAddressValidation(): void
    {
        $this->login('sophie@example.test');

        $response = $this->jsonMutation('POST', '/api/addresses', [
            'name' => '',
            'addressLine' => '12 rue des Lilas',
            'city' => 'Marseille',
        ], 422);

        self::assertSame('Données invalides.', $response['error']['message']);
        self::assertNotEmpty($response['error']['violations']);
    }

    public function testProfileCreateAndUpdate(): void
    {
        $this->login('sophie@example.test');

        self::assertNull($this->jsonGet('/api/profile')['profile']);

        $created = $this->jsonMutation('PUT', '/api/profile', [
            'displayName' => 'Sophie',
            'bio' => 'Passionnée de déco',
        ]);
        self::assertSame('Sophie', $created['profile']['displayName']);

        $updated = $this->jsonMutation('PUT', '/api/profile', [
            'displayName' => 'Sophie L.',
        ]);
        self::assertSame('Sophie L.', $updated['profile']['displayName']);
    }
}
