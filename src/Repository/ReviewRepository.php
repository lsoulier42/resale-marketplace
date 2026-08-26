<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\Review;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Review>
 */
class ReviewRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findByOrder(Order $order): ?Review
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.order = :order')
            ->setParameter('order', $order)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
