<?php

namespace App\Entity;

use App\Repository\SellerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SellerRepository::class)]
#[ORM\Table(name: 'seller')]
#[ORM\HasLifecycleCallbacks]
class Seller extends AbstractEntity
{
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private User $user;

    /** Compte Stripe Connect (onboarding vendeur·se). */
    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $stripeAccountId = null;

    /** Compte Stripe Connect prêt à recevoir des paiements. */
    #[ORM\Column]
    private bool $stripeAccountReady = false;

    /** @var Collection<int, Item> */
    #[ORM\OneToMany(targetEntity: Item::class, mappedBy: 'seller', cascade: ['persist', 'remove'])]
    private Collection $items;

    /** @var Collection<int, Media> */
    #[ORM\ManyToMany(targetEntity: Media::class)]
    #[ORM\JoinTable(name: 'seller_media')]
    private Collection $medias;

    /** @var Collection<int, Order> */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'seller')]
    private Collection $orders;

    /** @var Collection<int, Review> */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'toUser')]
    private Collection $reviews;

    public function __construct()
    {
        parent::__construct();
        $this->user    = new User();
        $this->items   = new ArrayCollection();
        $this->medias  = new ArrayCollection();
        $this->orders  = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getStripeAccountId(): ?string
    {
        return $this->stripeAccountId;
    }

    public function setStripeAccountId(?string $stripeAccountId): static
    {
        $this->stripeAccountId = $stripeAccountId;
        return $this;
    }

    public function isStripeAccountReady(): bool
    {
        return $this->stripeAccountReady;
    }

    public function setStripeAccountReady(bool $stripeAccountReady): static
    {
        $this->stripeAccountReady = $stripeAccountReady;
        return $this;
    }

    /** @return Collection<int, Item> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setSeller($this);
        }
        return $this;
    }

    public function removeItem(Item $item): static
    {
        $this->items->removeElement($item);
        return $this;
    }

    /** @return Collection<int, Media> */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): static
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
        }
        return $this;
    }

    public function removeMedia(Media $media): static
    {
        $this->medias->removeElement($media);
        return $this;
    }

    /** @return Collection<int, Order> */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    /** @return Collection<int, Review> */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }
}
