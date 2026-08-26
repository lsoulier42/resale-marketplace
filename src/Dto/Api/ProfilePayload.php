<?php

namespace App\Dto\Api;

use Symfony\Component\Validator\Constraints as Assert;

class ProfilePayload
{
    #[Assert\NotBlank(message: 'Le pseudo est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le pseudo est trop long (100 max).')]
    public string $displayName = '';

    public ?string $bio = null;
}
