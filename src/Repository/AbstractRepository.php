<?php

namespace App\Repository;

use App\Dto\PaginationDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @template T of object
 * @extends ServiceEntityRepository<T>
 */
abstract class AbstractRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     * @param class-string $className
     */
    public function __construct(ManagerRegistry $registry, string $className)
    {
        parent::__construct($registry, $className);
    }

    /**
     * @param mixed $entity
     * @param bool $flush
     * @return void
     */
    public function createOrUpdate(mixed $entity, bool $flush = true): void
    {
        $entityManager = $this->getEntityManager();
        if ($entity->getId() === null) {
            $entityManager->persist($entity);
        }
        if ($flush) {
            $entityManager->flush();
        }
    }

    /**
     * @param mixed $entity
     * @param bool $flush
     * @return void
     */
    public function remove(mixed $entity, bool $flush = true): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($entity);
        if ($flush) {
            $entityManager->flush();
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $fieldName
     * @param mixed $fieldValue
     * @return QueryBuilder
     */
    public static function addFieldLike(
        QueryBuilder $queryBuilder,
        string $alias,
        string $fieldName,
        mixed $fieldValue,
    ): QueryBuilder {
        $orx = new Orx();
        self::formatOrxLike($queryBuilder, $orx, $alias, $fieldName, $fieldValue);
        return $queryBuilder->andWhere($queryBuilder->expr()->orX($orx));
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Orx $orx
     * @param string $alias
     * @param string $fieldName
     * @param mixed $fieldValue
     * @return void
     */
    private static function formatOrxLike(
        QueryBuilder $queryBuilder,
        Orx $orx,
        string $alias,
        string $fieldName,
        mixed $fieldValue
    ): void {
        $fieldWithAlias = "$alias.$fieldName";
        $likeVersions = ["%$fieldValue%", "$fieldValue%", "%$fieldValue"];
        foreach ($likeVersions as $version) {
            $orx->add($queryBuilder->expr()->like($fieldWithAlias, $queryBuilder->expr()->literal($version)));
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $fieldName
     * @param mixed $fieldValue
     * @return QueryBuilder
     */
    public static function addFieldAndWhere(
        QueryBuilder $queryBuilder,
        string $alias,
        string $fieldName,
        mixed $fieldValue,
    ): QueryBuilder {
        $parameterName = $fieldName;
        return $queryBuilder->andWhere("$alias.$fieldName = :$parameterName")
            ->setParameter($parameterName, $fieldValue);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $parentAlias
     * @param string $relationField
     * @param string $childAlias
     * @param string $joinType
     * @return QueryBuilder
     */
    public static function addTableJoin(
        QueryBuilder $queryBuilder,
        string $parentAlias,
        string $relationField,
        string $childAlias,
        string $joinType = Join::LEFT_JOIN
    ): QueryBuilder {
        if (self::hasAlias($queryBuilder, $childAlias)) {
            return $queryBuilder;
        }
        $relation = "$parentAlias.$relationField";
        if ($joinType === Join::INNER_JOIN) {
            return $queryBuilder->innerJoin($relation, $childAlias);
        }
        return $queryBuilder->leftJoin($relation, $childAlias);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @return bool
     */
    private static function hasAlias(
        QueryBuilder $queryBuilder,
        string $alias
    ): bool {
        return in_array($alias, $queryBuilder->getAllAliases(), true);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return Collection<int, T>
     */
    public static function getCollectionFromQueryBuilder(QueryBuilder $queryBuilder): Collection
    {
        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $startDate
     * @param string $endDate
     * @param string $alias
     * @param string $fieldName
     * @return QueryBuilder
     */
    public static function addPeriodWhere(
        QueryBuilder $queryBuilder,
        string $startDate,
        string $endDate,
        string $alias,
        string $fieldName
    ): QueryBuilder {
        $startDateParameter = "startDate";
        $endDateParameter = "endDate";
        return $queryBuilder
            ->andWhere("$alias.$fieldName BETWEEN :$startDateParameter AND :$endDateParameter")
            ->setParameter($startDateParameter, $startDate)
            ->setParameter($endDateParameter, $endDate);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param int $numberElements
     * @return QueryBuilder
     */
    public static function addRandomElements(
        QueryBuilder $queryBuilder,
        string $alias,
        int $numberElements
    ): QueryBuilder {
        $numberElements = $numberElements > 0 ? $numberElements : null;
        $queryBuilder->addSelect('RANDOM() as HIDDEN rand');
        return $queryBuilder
            ->setMaxResults($numberElements)
            ->orderBy('rand');
    }

    /**
     * @param PaginationDto $dto
     * @param QueryBuilder $queryBuilder
     * @return Pagerfanta<T>
     */
    public static function findAllPaginated(PaginationDto $dto, QueryBuilder $queryBuilder): Pagerfanta
    {
        $adapter = new QueryAdapter($queryBuilder);
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage($dto->getLimit())
            ->setCurrentPage($dto->getPage());
        return $pagerFanta;
    }

    /**
     * Résout une entité par son uuid public (usage API / front).
     *
     * @return T|null
     */
    public function findByUuid(string $uuid): ?object
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
