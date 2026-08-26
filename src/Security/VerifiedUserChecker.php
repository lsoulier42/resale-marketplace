<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Bloque la connexion des comptes dont l'adresse email n'a pas encore
 * été confirmée (inscription sans clic sur le lien de confirmation).
 */
class VerifiedUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User || $user->isVerified()) {
            return;
        }

        throw new CustomUserMessageAccountStatusException(
            'Votre compte n\'est pas encore vérifié. Consultez votre boîte mail pour confirmer votre adresse email.'
        );
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }
}
