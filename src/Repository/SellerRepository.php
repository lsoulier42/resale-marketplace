<?php

namespace App\Repository;

use App\Entity\Seller;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Seller>
 */
class SellerRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seller::class);
    }

    public function findByUser(User $user): ?Seller
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
