<?php

namespace App\Dto\Api;

use Symfony\Component\Validator\Constraints as Assert;

class OrderStatusPayload
{
    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    public string $status = '';

    #[Assert\Length(max: 100, maxMessage: 'Le numéro de suivi est trop long (100 max).')]
    public ?string $trackingNumber = null;

    #[Assert\Length(max: 100, maxMessage: 'Le transporteur est trop long (100 max).')]
    public ?string $shippingProvider = null;
}
