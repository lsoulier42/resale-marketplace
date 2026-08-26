<?php

namespace App\Repository;

use App\Entity\Address;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Address>
 */
class AddressRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    /** @return Collection<int, Address> */
    public function findByUser(User $user): Collection
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.name', 'ASC');
        return self::getCollectionFromQueryBuilder($qb);
    }
}
