<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID            = 'paid';
    case SHIPPED         = 'shipped';
    case DELIVERED       = 'delivered';
    case CANCELLED       = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'En attente de paiement',
            self::PAID            => 'Payé',
            self::SHIPPED         => 'Expédié',
            self::DELIVERED       => 'Livré',
            self::CANCELLED       => 'Annulé',
        };
    }

    /** @return list<self> */
    public static function allowedTransitions(self $from): array
    {
        return match ($from) {
            self::PENDING_PAYMENT => [self::PAID, self::CANCELLED],
            self::PAID            => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED         => [self::DELIVERED],
            self::DELIVERED       => [],
            self::CANCELLED       => [],
        };
    }
}
