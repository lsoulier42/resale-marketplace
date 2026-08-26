<?php

namespace App\Dto\Api;

use Symfony\Component\Validator\Constraints as Assert;

class UserPayload
{
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'email est invalide.')]
    public string $email = '';

    public bool $isVerified = false;

    /** @var list<string> */
    public array $roles = [];

    /** Mot de passe : obligatoire à la création, optionnel à l'édition. */
    #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit faire au moins 8 caractères.')]
    public ?string $password = null;
}
