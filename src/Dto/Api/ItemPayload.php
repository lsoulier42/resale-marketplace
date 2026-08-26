<?php

namespace App\Dto\Api;

use Symfony\Component\Validator\Constraints as Assert;

class ItemPayload
{
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 200, maxMessage: 'Le titre est trop long (200 max).')]
    public string $title = '';

    public ?string $description = null;

    #[Assert\NotBlank(message: 'Le prix est obligatoire.')]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Le prix est invalide.')]
    public string $price = '0.00';

    #[Assert\Range(min: 0, max: 100000, notInRangeMessage: 'La quantité doit être entre 0 et 100000.')]
    public int $availableCount = 1;

    #[Assert\NotBlank(message: 'La catégorie est obligatoire.')]
    public string $categoryUuid = '';

    /** @var list<string> */
    public array $mediaUuids = [];
}
