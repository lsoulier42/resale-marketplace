<?php

namespace App\Repository;

use App\Entity\Profile;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Profile>
 */
class ProfileRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    public function findByUser(User $user): ?Profile
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
