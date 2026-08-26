<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

// fromUser = the Customer writing the review
// toUser   = the Seller receiving the review

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'review')]
#[ORM\HasLifecycleCallbacks]
class Review extends AbstractEntity
{
    #[ORM\Column]
    #[Assert\Range(min: 1, max: 5)]
    private int $star = 5;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\OneToOne(targetEntity: Order::class)]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private Order $order;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(name: 'from_customer_id', nullable: false, onDelete: 'CASCADE')]
    private Customer $fromUser;

    #[ORM\ManyToOne(targetEntity: Seller::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(name: 'to_seller_id', nullable: false, onDelete: 'CASCADE')]
    private Seller $toUser;

    public function __construct()
    {
        parent::__construct();
        $this->order    = new Order();
        $this->fromUser = new Customer();
        $this->toUser   = new Seller();
    }

    public function getStar(): int
    {
        return $this->star;
    }

    public function setStar(int $star): static
    {
        $this->star = $star;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): static
    {
        $this->order = $order;
        return $this;
    }

    public function getFromUser(): Customer
    {
        return $this->fromUser;
    }

    public function setFromUser(Customer $fromUser): static
    {
        $this->fromUser = $fromUser;
        return $this;
    }

    public function getToUser(): Seller
    {
        return $this->toUser;
    }

    public function setToUser(Seller $toUser): static
    {
        $this->toUser = $toUser;
        return $this;
    }
}
