<?php

namespace App\Dto\Api;

use Symfony\Component\Validator\Constraints as Assert;

class ReviewPayload
{
    #[Assert\NotBlank(message: 'La commande est obligatoire.')]
    public string $orderUuid = '';

    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note doit être entre 1 et 5 étoiles.')]
    public int $star = 5;

    public ?string $comment = null;
}
