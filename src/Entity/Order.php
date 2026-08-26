<?php

namespace App\Entity;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'shop_order')]
#[ORM\HasLifecycleCallbacks]
class Order extends AbstractEntity
{
    #[ORM\Column(length: 64, unique: true)]
    private string $reference = '';

    /**
     * Owning side — FK lives on this table.
     */
    #[ORM\OneToOne(targetEntity: Item::class, inversedBy: 'order')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Item $item;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Customer $customer;

    #[ORM\ManyToOne(targetEntity: Seller::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Seller $seller;

    /** Session de paiement Stripe Checkout créée pour cette commande. */
    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $stripeCheckoutSessionId = null;

    /** PaymentIntent Stripe (renseigné par le webhook au paiement). */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePaymentIntentId = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $trackingNumber = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $shippingProvider = null;

    #[ORM\ManyToOne(targetEntity: Address::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Address $shippingAddress = null;

    #[ORM\Column(type: Types::STRING, enumType: OrderStatus::class, length: 30)]
    private OrderStatus $status = OrderStatus::PENDING_PAYMENT;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $shippingFee = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $totalPrice = '0.00';

    public function __construct()
    {
        parent::__construct();
        $this->item      = new Item();
        $this->customer  = new Customer();
        $this->seller    = new Seller();
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;
        return $this;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): static
    {
        $this->item = $item;
        return $this;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): static
    {
        $this->customer = $customer;
        return $this;
    }

    public function getSeller(): Seller
    {
        return $this->seller;
    }

    public function setSeller(Seller $seller): static
    {
        $this->seller = $seller;
        return $this;
    }

    public function getStripeCheckoutSessionId(): ?string
    {
        return $this->stripeCheckoutSessionId;
    }

    public function setStripeCheckoutSessionId(?string $stripeCheckoutSessionId): static
    {
        $this->stripeCheckoutSessionId = $stripeCheckoutSessionId;
        return $this;
    }

    public function getStripePaymentIntentId(): ?string
    {
        return $this->stripePaymentIntentId;
    }

    public function setStripePaymentIntentId(?string $stripePaymentIntentId): static
    {
        $this->stripePaymentIntentId = $stripePaymentIntentId;
        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }

    public function getShippingProvider(): ?string
    {
        return $this->shippingProvider;
    }

    public function setShippingProvider(?string $shippingProvider): static
    {
        $this->shippingProvider = $shippingProvider;
        return $this;
    }

    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?Address $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getShippingFee(): string
    {
        return $this->shippingFee;
    }

    public function setShippingFee(string $shippingFee): static
    {
        $this->shippingFee = $shippingFee;
        return $this;
    }

    public function getTotalPrice(): string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(string $totalPrice): static
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }
}
