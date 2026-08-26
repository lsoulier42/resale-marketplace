<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Repository\ProfileRepository;

/**
 * Construit les payloads JSON des commandes (espace client).
 */
class OrderPresenter
{
    public function __construct(
        private readonly CatalogPresenter $catalogPresenter,
        private readonly ProfileRepository $profileRepository,
    ) {
    }

    /**
     * @param array{canUpdateStatus: bool, canPay: bool, canReview: bool, hasReview: bool} $extra
     * @return array<string, mixed>
     */
    public function detail(Order $order, array $extra): array
    {
        $item = $this->catalogPresenter->itemCard($order->getItem());

        $profile = $this->profileRepository->findByUser($order->getSeller()->getUser());

        return [
            'uuid' => (string) $order->getUuid(),
            'reference' => $order->getReference(),
            'status' => $order->getStatus()->value,
            'statusLabel' => $order->getStatus()->label(),
            'totalPrice' => $order->getTotalPrice(),
            'shippingFee' => $order->getShippingFee(),
            'trackingNumber' => $order->getTrackingNumber(),
            'shippingProvider' => $order->getShippingProvider(),
            'createdAt' => $order->getCreatedAt()?->format('c'),
            'item' => $item,
            'seller' => [
                'uuid' => (string) $order->getSeller()->getUuid(),
                'displayName' => $profile?->getDisplayName(),
            ],
            'shippingAddress' => $order->getShippingAddress() !== null
                ? $this->address($order->getShippingAddress())
                : null,
            'allowedTransitions' => array_map(
                fn (OrderStatus $status): array => ['value' => $status->value, 'label' => $status->label()],
                OrderStatus::allowedTransitions($order->getStatus())
            ),
            'canUpdateStatus' => $extra['canUpdateStatus'],
            'canPay' => $extra['canPay'],
            'canReview' => $extra['canReview'],
            'hasReview' => $extra['hasReview'],
        ];
    }

    /** @return array<string, string> */
    private function address(Address $address): array
    {
        return [
            'uuid' => (string) $address->getUuid(),
            'name' => $address->getName(),
            'addressLine' => $address->getAddressLine(),
            'city' => $address->getCity(),
            'zipCode' => $address->getZipCode(),
            'country' => $address->getCountry(),
        ];
    }
}
