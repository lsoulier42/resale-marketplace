<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Tests fonctionnels de l'espace vendeur·se & administration :
 * publication d'articles, médias, gestion users/customers.
 */
class SellerAdminApiTest extends ApiTestCase
{
    public function testNonSellerCannotCreateItem(): void
    {
        $this->login('camille@example.test');

        $this->jsonMutation('POST', '/api/items', [
            'title' => 'Test',
            'price' => '10.00',
            'categoryUuid' => $this->jsonGet('/api/categories')['categories'][0]['uuid'],
        ], 422);
    }

    public function testSellerCreatesAndUpdatesItem(): void
    {
        $this->login('jordan@example.test');
        $categoryUuid = $this->jsonGet('/api/categories')['categories'][0]['uuid'];

        $created = $this->jsonMutation('POST', '/api/items', [
            'title' => 'Veste édition limitée',
            'description' => 'Très demandée',
            'price' => '25.00',
            'availableCount' => 3,
            'categoryUuid' => $categoryUuid,
            'mediaUuids' => [],
        ], 201);

        $uuid = $created['item']['uuid'];
        self::assertSame('Veste édition limitée', $created['item']['title']);

        $updated = $this->jsonMutation('PUT', '/api/items/' . $uuid, [
            'title' => 'Veste édition limitée v2',
            'price' => '26.50',
            'availableCount' => 2,
            'categoryUuid' => $categoryUuid,
            'mediaUuids' => [],
        ]);
        self::assertSame('Veste édition limitée v2', $updated['item']['title']);
        self::assertSame('26.50', $updated['item']['price']);
    }

    public function testSellerCannotEditOthersItem(): void
    {
        $this->login('jordan@example.test');
        $categoryUuid = $this->jsonGet('/api/categories')['categories'][0]['uuid'];
        $uuid = $this->jsonMutation('POST', '/api/items', [
            'title' => 'Article de Jordan',
            'price' => '10.00',
            'categoryUuid' => $categoryUuid,
        ], 201)['item']['uuid'];

        $this->login('sam@example.test');
        $this->jsonMutation('PUT', '/api/items/' . $uuid, [
            'title' => 'Piraté',
            'price' => '1.00',
            'categoryUuid' => $categoryUuid,
        ], 403);

        $this->jsonMutation('DELETE', '/api/items/' . $uuid, null, 403);
    }

    public function testOrderedItemCannotBeDeleted(): void
    {
        $available = $this->availableItemWithSeller();

        $this->login('camille@example.test');
        $orderUuid = $this->jsonMutation('POST', '/api/orders', [
            'itemUuid' => $available['itemUuid'],
        ], 201)['order']['uuid'];

        $this->login($available['sellerEmail']);
        $this->jsonMutation('DELETE', '/api/items/' . $available['itemUuid'], null, 422);

        // La commande reste accessible.
        self::assertSame($orderUuid, $this->jsonGet('/api/orders/' . $orderUuid)['order']['uuid']);
    }

    public function testMediaUploadAndDelete(): void
    {
        $this->login('jordan@example.test');

        $path = sys_get_temp_dir() . '/resale-test-upload.svg';
        file_put_contents($path, '<svg xmlns="http://www.w3.org/2000/svg" width="8" height="8"/>');

        $this->client->request('POST', '/api/medias', [], [
            'file' => new UploadedFile($path, 'resale-test-upload.svg', 'image/svg+xml'),
        ], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CSRF_TOKEN' => $this->fetchCsrfToken(),
        ]);
        self::assertResponseStatusCodeSame(201);

        $content = (string) $this->client->getResponse()->getContent();
        $media = json_decode($content, true, 512, JSON_THROW_ON_ERROR)['media'];
        self::assertStringStartsWith('/uploads/', $media['fileUrl']);
        self::assertSame('image/svg+xml', $media['fileType']);

        $this->jsonMutation('DELETE', '/api/medias/' . $media['uuid']);
        @unlink($path);
    }

    public function testAdminUserCrudAndSelfProtection(): void
    {
        $this->login('admin@example.test');

        $created = $this->jsonMutation('POST', '/api/users', [
            'email' => 'dirigeante@example.test',
            'password' => 'secret123',
            'roles' => ['ROLE_ADMIN'],
            'isVerified' => true,
        ], 201);
        $uuid = $created['user']['uuid'];
        self::assertContains('ROLE_ADMIN', $created['user']['roles']);

        $updated = $this->jsonMutation('PUT', '/api/users/' . $uuid, [
            'email' => 'dirigeante@example.test',
            'roles' => ['ROLE_USER'],
        ]);
        self::assertSame(['ROLE_USER'], $updated['user']['roles']);

        // Protections anti auto-destruction.
        $selfUuid = $this->jsonGet('/api/me')['user']['uuid'];
        $this->jsonMutation('PUT', '/api/users/' . $selfUuid, [
            'email' => 'admin@example.test',
            'roles' => ['ROLE_USER'],
        ], 422);
        $this->jsonMutation('DELETE', '/api/users/' . $selfUuid, null, 422);

        // Suppression d'un autre utilisateur.
        $this->jsonMutation('DELETE', '/api/users/' . $uuid);
    }

    public function testAdminOnlyEndpointsRejectUsers(): void
    {
        $this->login('camille@example.test');

        $this->client->request('GET', '/api/users', [], [], ['HTTP_ACCEPT' => 'application/json']);
        self::assertResponseStatusCodeSame(403);

        $this->client->request('GET', '/api/customers', [], [], ['HTTP_ACCEPT' => 'application/json']);
        self::assertResponseStatusCodeSame(403);
    }

    public function testCustomersIndex(): void
    {
        $this->login('admin@example.test');

        $data = $this->jsonGet('/api/customers');

        self::assertNotEmpty($data['customers']);
        foreach ($data['customers'] as $customer) {
            self::assertArrayHasKey('email', $customer);
            self::assertArrayHasKey('orderCount', $customer);
        }
    }
}
