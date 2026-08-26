<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table(name: 'customer')]
#[ORM\HasLifecycleCallbacks]
class Customer extends AbstractEntity
{
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private User $user;

    /** @var Collection<int, Order> */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'customer')]
    private Collection $orders;

    /** @var Collection<int, Media> */
    #[ORM\ManyToMany(targetEntity: Media::class)]
    #[ORM\JoinTable(name: 'customer_media')]
    private Collection $medias;

    /** @var Collection<int, Review> */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'fromUser')]
    private Collection $reviews;

    public function __construct()
    {
        parent::__construct();
        $this->user    = new User();
        $this->orders  = new ArrayCollection();
        $this->medias  = new ArrayCollection();
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

    /** @return Collection<int, Order> */
    public function getOrders(): Collection
    {
        return $this->orders;
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

    /** @return Collection<int, Review> */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }
}
