<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ORM\Table(name: 'address')]
#[ORM\HasLifecycleCallbacks]
class Address extends AbstractEntity
{
    #[ORM\Column(length: 255)]
    private string $addressLine = '';

    #[ORM\Column(length: 100)]
    private string $city = '';

    #[ORM\Column(length: 20)]
    private string $zipCode = '';

    #[ORM\Column(length: 100)]
    private string $country = '';

    #[ORM\Column(length: 100)]
    private string $name = '';

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    public function __construct()
    {
        parent::__construct();
        $this->user = new User();
    }

    public function getAddressLine(): string
    {
        return $this->addressLine;
    }

    public function setAddressLine(string $addressLine): static
    {
        $this->addressLine = $addressLine;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
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
}
