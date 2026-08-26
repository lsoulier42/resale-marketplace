<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\Seller;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Item>
 */
class ItemRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    /** @return Collection<int, Item> */
    public function findAvailable(): Collection
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.order', 'o')
            ->andWhere('i.availableCount > 0')
            ->andWhere('o.id IS NULL')
            ->orderBy('i.createdAt', 'DESC');
        return self::getCollectionFromQueryBuilder($qb);
    }

    /** @return Collection<int, Item> */
    public function findBySeller(Seller $seller): Collection
    {
        $qb = $this->createQueryBuilder('i')
            ->andWhere('i.seller = :seller')
            ->setParameter('seller', $seller)
            ->orderBy('i.createdAt', 'DESC');
        return self::getCollectionFromQueryBuilder($qb);
    }

    public function countAvailableByCategory(Category $category): int
    {
        return $this->countAvailable('category', $category);
    }

    public function countAvailableBySeller(Seller $seller): int
    {
        return $this->countAvailable('seller', $seller);
    }

    /**
     * @param Category|Seller $entity
     */
    private function countAvailable(string $paramName, object $entity): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->leftJoin('i.order', 'o')
            ->andWhere('i.availableCount > 0')
            ->andWhere('o.id IS NULL')
            ->andWhere("i.$paramName = :$paramName")
            ->setParameter($paramName, $entity)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
