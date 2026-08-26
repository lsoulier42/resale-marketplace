<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Media>
 */
class MediaRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }
}
