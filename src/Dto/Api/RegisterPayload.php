<?php

namespace App\Dto\Api;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterPayload
{
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'email est invalide.')]
    public string $email = '';

    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit faire au moins 8 caractères.')]
    public string $password = '';

    #[Assert\NotBlank(message: 'Le pseudo est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le pseudo est trop long (100 max).')]
    public string $displayName = '';
}
