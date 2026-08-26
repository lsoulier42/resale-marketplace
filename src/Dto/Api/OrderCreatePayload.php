<?php

namespace App\Dto\Api;

use Symfony\Component\Validator\Constraints as Assert;

class OrderCreatePayload
{
    #[Assert\NotBlank(message: 'L\'article est obligatoire.')]
    public string $itemUuid = '';

    public ?string $addressUuid = null;

    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Les frais de livraison sont invalides.')]
    public ?string $shippingFee = null;
}
