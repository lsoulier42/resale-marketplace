<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\Table(name: 'item')]
#[ORM\HasLifecycleCallbacks]
class Item extends AbstractEntity
{
    #[ORM\Column(length: 200)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $price = '0.00';

    #[ORM\Column]
    private int $availableCount = 1;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\ManyToOne(targetEntity: Seller::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Seller $seller;

    /** @var Collection<int, Media> */
    #[ORM\ManyToMany(targetEntity: Media::class)]
    #[ORM\JoinTable(name: 'item_media')]
    private Collection $medias;

    /**
     * Inverse side — the FK lives on Order, not here.
     */
    #[ORM\OneToOne(targetEntity: Order::class, mappedBy: 'item')]
    private ?Order $order = null;

    public function __construct()
    {
        parent::__construct();
        $this->medias   = new ArrayCollection();
        $this->category = new Category();
        $this->seller   = new Seller();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getAvailableCount(): int
    {
        return $this->availableCount;
    }

    public function setAvailableCount(int $availableCount): static
    {
        $this->availableCount = $availableCount;
        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;
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

    /**
     * Un article est « Vendu » dès qu'il est lié à une commande
     * (achat unitaire) ou que son stock est épuisé.
     */
    public function isSold(): bool
    {
        return $this->order !== null || $this->availableCount <= 0;
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

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;
        return $this;
    }
}
