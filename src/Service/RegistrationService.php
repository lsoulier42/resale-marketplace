<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Api\RegisterPayload;
use App\Entity\Customer;
use App\Entity\Profile;
use App\Entity\Seller;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\ProfileRepository;
use App\Repository\SellerRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Twig\Environment;

/**
 * Crée un compte (client·e et/ou vendeur·se) et envoie l'email de confirmation.
 *
 * Un compte est créé non vérifié : il ne peut se connecter qu'après avoir
 * confirmé son adresse email via le lien envoyé par mail (Mailpit en dev).
 */
class RegistrationService
{
    private const TOKEN_TTL = '+24 hours';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CustomerRepository $customerRepository,
        private readonly SellerRepository $sellerRepository,
        private readonly ProfileRepository $profileRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        #[Autowire('%env(APP_BASE_URL)%')]
        private readonly string $appBaseUrl,
        #[Autowire('%env(MAILER_FROM)%')]
        private readonly string $mailerFrom,
    ) {
    }

    /**
     * Crée le compte (User non vérifié + Profile + Customer, et Seller
     * si demandé), génère le jeton de confirmation et envoie l'email.
     */
    public function register(RegisterPayload $payload, bool $asSeller = false): User
    {
        $user = new User();
        $user->setEmail($payload->email)
            ->setRoles(['ROLE_USER'])
            ->setIsVerified(false)
            ->setPassword($this->passwordHasher->hashPassword($user, $payload->password));

        $profile = (new Profile())->setUser($user)->setDisplayName($payload->displayName);
        $customer = (new Customer())->setUser($user);

        $this->userRepository->createOrUpdate($user);
        $this->profileRepository->createOrUpdate($profile);
        $this->customerRepository->createOrUpdate($customer);

        if ($asSeller) {
            $seller = (new Seller())->setUser($user);
            $this->sellerRepository->createOrUpdate($seller);
        }

        $this->issueConfirmationToken($user);
        $this->userRepository->createOrUpdate($user);
        $this->sendConfirmationEmail($user);

        return $user;
    }

    /**
     * Régénère le jeton d'un compte non vérifié et renvoie l'email.
     */
    public function resendConfirmation(User $user): void
    {
        if ($user->isVerified()) {
            return;
        }

        $this->issueConfirmationToken($user);
        $this->userRepository->createOrUpdate($user);
        $this->sendConfirmationEmail($user);
    }

    private function issueConfirmationToken(User $user): void
    {
        $user->setConfirmationToken(bin2hex(random_bytes(32)))
            ->setConfirmationTokenExpiresAt(new \DateTimeImmutable(self::TOKEN_TTL));
    }

    private function sendConfirmationEmail(User $user): void
    {
        $token = $user->getConfirmationToken();
        if ($token === null) {
            return;
        }

        $confirmationUrl = $this->appBaseUrl . '/confirm-email?token=' . urlencode($token);
        $context = ['confirmationUrl' => $confirmationUrl];

        $email = (new Email())
            ->from(new Address($this->mailerFrom, 'Resale Marketplace'))
            ->to($user->getEmail())
            ->subject('Confirmez votre compte Resale Marketplace')
            ->html($this->twig->render('emails/confirmation.html.twig', $context))
            ->text($this->twig->render('emails/confirmation.txt.twig', $context));

        $this->mailer->send($email);
    }
}
