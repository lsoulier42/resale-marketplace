<?php

namespace App\Tests\Api;

/**
 * Tests fonctionnels de l'API JSON publique (vitrine).
 */
class PublicApiTest extends ApiTestCase
{
    public function testHomeReturnsFeaturedItemsAndCategories(): void
    {
        $data = $this->jsonGet('/api/home');

        self::assertArrayHasKey('featuredItems', $data);
        self::assertArrayHasKey('categories', $data);
        self::assertNotEmpty($data['featuredItems']);
        self::assertNotEmpty($data['categories']);

        $item = $data['featuredItems'][0];
        self::assertArrayHasKey('uuid', $item);
        self::assertArrayHasKey('title', $item);
        self::assertArrayHasKey('price', $item);
        self::assertArrayHasKey('isSold', $item);
        self::assertArrayHasKey('medias', $item);
        self::assertArrayHasKey('seller', $item);
    }

    public function testItemsArePaginated(): void
    {
        $data = $this->jsonGet('/api/items?page=1&limit=3');

        self::assertCount(3, $data['items']);
        self::assertSame(1, $data['page']);
        self::assertSame(3, $data['limit']);
        self::assertSame(5, $data['total']);
        self::assertSame(2, $data['pages']);

        $secondPage = $this->jsonGet('/api/items?page=2&limit=3');
        self::assertCount(2, $secondPage['items']);
    }

    public function testItemsCanBeFilteredByCategory(): void
    {
        $categories = $this->jsonGet('/api/categories');
        $categoryUuid = $categories['categories'][0]['uuid'];

        $data = $this->jsonGet('/api/items?category=' . $categoryUuid);

        self::assertNotEmpty($data['items']);
        foreach ($data['items'] as $item) {
            self::assertSame($categoryUuid, $item['category']['uuid']);
        }
    }

    public function testItemShowByUuid(): void
    {
        $list = $this->jsonGet('/api/items?limit=1');
        $uuid = $list['items'][0]['uuid'];

        $data = $this->jsonGet('/api/items/' . $uuid);

        self::assertSame($uuid, $data['item']['uuid']);
        self::assertArrayHasKey('description', $data['item']);
    }

    public function testUnknownItemReturns404(): void
    {
        $this->client->request(
            'GET',
            '/api/items/00000000-0000-0000-0000-000000000000',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testSellersExposeDisplayName(): void
    {
        $data = $this->jsonGet('/api/sellers');

        self::assertNotEmpty($data['sellers']);
        foreach ($data['sellers'] as $seller) {
            self::assertArrayHasKey('uuid', $seller);
            self::assertArrayHasKey('displayName', $seller);
            self::assertArrayHasKey('itemCount', $seller);
        }
    }

    public function testReviewsExposeRecipients(): void
    {
        $data = $this->jsonGet('/api/reviews');

        self::assertNotEmpty($data['reviews']);
        foreach ($data['reviews'] as $review) {
            self::assertArrayHasKey('star', $review);
            self::assertArrayHasKey('toUser', $review);
            self::assertArrayHasKey('displayName', $review['toUser']);
            self::assertArrayHasKey('orderReference', $review);
        }
    }
}
