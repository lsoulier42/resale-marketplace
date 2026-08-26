<?php

namespace App\Tests\Api;

use App\Entity\User;

/**
 * Tests fonctionnels du module d'inscription (client·e & vendeur·se)
 * et de la confirmation d'email.
 */
class RegistrationApiTest extends ApiTestCase
{
    public function testRegisterCreatesCustomerAndProfile(): void
    {
        $this->registerAndConfirm('fraiche@example.test', 'secret123', 'Fraîche');

        $this->login('fraiche@example.test', 'secret123');
        $me = $this->jsonGet('/api/me');
        self::assertTrue($me['isCustomer']);
        self::assertFalse($me['isSeller']);
        self::assertTrue($me['user']['isVerified']);

        $profile = $this->jsonGet('/api/profile')['profile'];
        self::assertSame('Fraîche', $profile['displayName']);
    }

    public function testRegisterSellerCreatesSellerAndCustomer(): void
    {
        $this->registerAndConfirm(
            'vendeur@example.test',
            'secret123',
            'Vendeur',
            '/api/register/seller'
        );

        $this->login('vendeur@example.test', 'secret123');
        $me = $this->jsonGet('/api/me');
        self::assertTrue($me['isSeller']);
        self::assertTrue($me['isCustomer'], 'Un compte vendeur·se reste client·e (il·elle peut acheter).');
    }

    public function testRegisterRejectsDuplicateEmailAndWeakPassword(): void
    {
        $this->jsonMutation('POST', '/api/register', [
            'email' => 'duplicata@example.test',
            'password' => 'secret123',
            'displayName' => 'Dup',
        ], 201);

        $duplicate = $this->jsonMutation('POST', '/api/register', [
            'email' => 'duplicata@example.test',
            'password' => 'secret123',
            'displayName' => 'Dup',
        ], 422);
        self::assertSame('Cet email est déjà utilisé.', $duplicate['error']['message']);

        $weak = $this->jsonMutation('POST', '/api/register', [
            'email' => 'faible@example.test',
            'password' => 'abc',
            'displayName' => 'Faible',
        ], 422);
        self::assertNotEmpty($weak['error']['violations']);
    }

    public function testLoginRejectedUntilEmailConfirmed(): void
    {
        $this->jsonMutation('POST', '/api/register', [
            'email' => 'enattente@example.test',
            'password' => 'secret123',
            'displayName' => 'Attente',
        ], 201);

        $this->client->request('POST', '/api/login', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_CSRF_TOKEN' => $this->fetchCsrfToken(),
        ], json_encode([
            'email' => 'enattente@example.test',
            'password' => 'secret123',
        ], JSON_THROW_ON_ERROR));
        self::assertResponseStatusCodeSame(401);

        $data = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertStringContainsString('pas encore vérifié', $data['error']['message']);
    }

    public function testConfirmEmailWithInvalidToken(): void
    {
        $response = $this->jsonGetStatus('/api/register/confirm/' . str_repeat('a', 64), 400);
        self::assertSame('Lien de confirmation invalide ou expiré.', $response['error']['message']);
    }

    public function testConfirmEmailWithExpiredToken(): void
    {
        $this->jsonMutation('POST', '/api/register', [
            'email' => 'expiree@example.test',
            'password' => 'secret123',
            'displayName' => 'Expirée',
        ], 201);

        $token = $this->extractConfirmationToken();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'expiree@example.test']);
        self::assertNotNull($user);
        $user->setConfirmationTokenExpiresAt(new \DateTimeImmutable('-1 hour'));
        $entityManager->flush();

        $this->jsonGetStatus('/api/register/confirm/' . $token, 400);
    }

    public function testResendConfirmationEmail(): void
    {
        $this->jsonMutation('POST', '/api/register', [
            'email' => 'renvoi@example.test',
            'password' => 'secret123',
            'displayName' => 'Renvoi',
        ], 201);

        // La collecte mailer est remise à zéro à chaque requête : le resend
        // doit produire un (nouvel) email de confirmation.
        $this->jsonMutation('POST', '/api/register/resend-confirmation', [
            'email' => 'renvoi@example.test',
        ]);
        self::assertEmailCount(1);

        $this->jsonGetStatus('/api/register/confirm/' . $this->extractConfirmationToken(), 200);
        $this->login('renvoi@example.test', 'secret123');
    }

    public function testResendConfirmationIsGenericForUnknownEmail(): void
    {
        $response = $this->jsonMutation('POST', '/api/register/resend-confirmation', [
            'email' => 'inconnu@example.test',
        ]);
        self::assertTrue($response['sent']);
        self::assertEmailCount(0);
    }

    public function testCustomerCanBecomeSeller(): void
    {
        $this->login('camille@example.test');

        $response = $this->jsonMutation('POST', '/api/me/seller', null, 201);
        self::assertTrue($response['isSeller']);

        $me = $this->jsonGet('/api/me');
        self::assertTrue($me['isSeller']);

        // Le·la nouveau·elle vendeur·se peut publier un article.
        $categoryUuid = $this->jsonGet('/api/categories')['categories'][0]['uuid'];
        $created = $this->jsonMutation('POST', '/api/items', [
            'title' => 'Ma première paire',
            'price' => '15.00',
            'categoryUuid' => $categoryUuid,
        ], 201);
        self::assertSame('Ma première paire', $created['item']['title']);
    }

    public function testBecomeSellerTwiceRejected(): void
    {
        $this->login('jordan@example.test');
        $this->jsonMutation('POST', '/api/me/seller', null, 422);
    }
}
