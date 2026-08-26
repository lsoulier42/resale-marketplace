<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Seller;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Order>
 */
class OrderRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /** @return Collection<int, Order> */
    public function findByCustomer(Customer $customer): Collection
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('o.createdAt', 'DESC');
        return self::getCollectionFromQueryBuilder($qb);
    }

    /** @return Collection<int, Order> */
    public function findBySeller(Seller $seller): Collection
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.seller = :seller')
            ->setParameter('seller', $seller)
            ->orderBy('o.createdAt', 'DESC');
        return self::getCollectionFromQueryBuilder($qb);
    }
}
