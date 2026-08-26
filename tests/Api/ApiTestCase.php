<?php

namespace App\Tests\Api;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

/**
 * Base des tests fonctionnels de l'API JSON.
 *
 * Les fixtures sont rechargées (purge + load) une fois par classe :
 * chaque classe part donc d'un jeu de données frais. Les tests d'une
 * même classe sont conçus pour rester indépendants malgré les
 * mutations cumulées (chaque test crée ses propres données).
 */
abstract class ApiTestCase extends WebTestCase
{
    use MailerAssertionsTrait;

    protected const DEMO_PASSWORD = 'demo1234';

    protected KernelBrowser $client;

    public static function setUpBeforeClass(): void
    {
        static::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        /** @var \Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader $loader */
        $loader = $container->get('doctrine.fixtures.loader');

        $executor = new ORMExecutor($entityManager, new ORMPurger($entityManager));
        $executor->execute($loader->getFixtures());
        static::ensureKernelShutdown();
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function login(string $email, string $password = self::DEMO_PASSWORD): void
    {
        $this->client->request('POST', '/api/login', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_CSRF_TOKEN' => $this->fetchCsrfToken(),
        ], json_encode(['email' => $email, 'password' => $password], JSON_THROW_ON_ERROR));

        self::assertResponseIsSuccessful();
    }

    protected function fetchCsrfToken(): string
    {
        $this->client->request('GET', '/api/csrf-token', [], [], ['HTTP_ACCEPT' => 'application/json']);
        self::assertResponseIsSuccessful();

        $data = $this->decodeResponse();

        return (string) $data['token'];
    }

    /** @return array<string, mixed> */
    protected function jsonGet(string $path): array
    {
        $this->client->request('GET', $path, [], [], ['HTTP_ACCEPT' => 'application/json']);
        self::assertResponseIsSuccessful();

        return $this->decodeResponse();
    }

    /** @return array<string, mixed> */
    protected function jsonGetStatus(string $path, int $expectedStatus): array
    {
        $this->client->request('GET', $path, [], [], ['HTTP_ACCEPT' => 'application/json']);
        self::assertResponseStatusCodeSame($expectedStatus);

        return $this->decodeResponse();
    }

    /**
     * Inscrit un compte via l'API puis confirme son email avec le jeton
     * reçu par mail (le compte est alors connectable).
     */
    protected function registerAndConfirm(
        string $email,
        string $password = self::DEMO_PASSWORD,
        string $displayName = 'Test',
        string $path = '/api/register'
    ): void {
        $this->jsonMutation('POST', $path, [
            'email' => $email,
            'password' => $password,
            'displayName' => $displayName,
        ], 201);

        $this->jsonGetStatus('/api/register/confirm/' . $this->extractConfirmationToken(), 200);
    }

    /**
     * Extrait le jeton de confirmation du dernier email envoyé.
     */
    protected function extractConfirmationToken(): string
    {
        $messages = self::getMailerMessages();
        self::assertNotEmpty($messages, 'Aucun email envoyé.');
        $message = $messages[count($messages) - 1];
        self::assertInstanceOf(Email::class, $message);
        $body = (string) $message->getTextBody();
        self::assertMatchesRegularExpression('#/confirm-email\?token=[a-f0-9]{64}#', $body);
        preg_match('#/confirm-email\?token=([a-f0-9]{64})#', $body, $matches);

        return $matches[1];
    }

    /**
     * @param array<string, mixed>|null $body
     * @return array<string, mixed>
     */
    protected function jsonMutation(string $method, string $path, ?array $body = null, int $expectedStatus = 200): array
    {
        $server = [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CSRF_TOKEN' => $this->fetchCsrfToken(),
        ];
        $content = null;
        if ($body !== null) {
            $server['CONTENT_TYPE'] = 'application/json';
            $content = json_encode($body, JSON_THROW_ON_ERROR);
        }

        $this->client->request($method, $path, [], [], $server, $content);
        self::assertResponseStatusCodeSame($expectedStatus);

        return $this->decodeResponse();
    }

    /** @return array<string, mixed> */
    private function decodeResponse(): array
    {
        return json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Retourne un article disponible quelconque + l'email de son vendeur·se,
     * en contournant la dépendance à un stock précis des fixtures.
     *
     * @return array{itemUuid: string, sellerEmail: string}
     */
    protected function availableItemWithSeller(): array
    {
        $sellers = $this->jsonGet('/api/sellers')['sellers'];
        foreach ($sellers as $seller) {
            $items = $this->jsonGet('/api/sellers/' . $seller['uuid'])['items'];
            if ($items !== []) {
                return [
                    'itemUuid' => $items[0]['uuid'],
                    'sellerEmail' => $this->sellerEmail((string) $seller['displayName']),
                ];
            }
        }
        self::fail('Aucun·e vendeur·se avec des articles disponibles dans les fixtures.');
    }

    private function sellerEmail(string $displayName): string
    {
        return match ($displayName) {
            'Alex' => 'alex@example.test',
            'Sam' => 'sam@example.test',
            'Jordan' => 'jordan@example.test',
            default => throw new \RuntimeException('Vendeur·se inconnu·e dans les fixtures : ' . $displayName),
        };
    }
}
