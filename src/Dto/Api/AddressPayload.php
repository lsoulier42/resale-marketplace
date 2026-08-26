<?php

namespace App\Dto\Api;

use Symfony\Component\Validator\Constraints as Assert;

class AddressPayload
{
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le nom est trop long (100 max).')]
    public string $name = '';

    #[Assert\NotBlank(message: 'L\'adresse est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'L\'adresse est trop longue (255 max).')]
    public string $addressLine = '';

    #[Assert\NotBlank(message: 'La ville est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'La ville est trop longue (100 max).')]
    public string $city = '';

    #[Assert\NotBlank(message: 'Le code postal est obligatoire.')]
    #[Assert\Length(max: 20, maxMessage: 'Le code postal est trop long (20 max).')]
    public string $zipCode = '';

    #[Assert\NotBlank(message: 'Le pays est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le pays est trop long (100 max).')]
    public string $country = '';
}
