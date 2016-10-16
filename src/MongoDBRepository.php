<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;

/**
 * MongoDB document repository.
 */
class MongoDBRepository extends DocumentRepository implements Repository
{
    use RepositoryTrait;

    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return $this->getDocumentManager();
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return ClassUtils::getRealClass(parent::getClassName());
    }

    /**
     * {@inheritdoc}
     *
     * @param array|Builder $criteria
     * @param array         $orderBy
     * @param int           $limit
     * @param int           $offset
     *
     * @throws \InvalidArgumentException
     *
     * @return \Jgut\Doctrine\Repository\Pager\Page
     */
    public function findPagedBy($criteria, array $orderBy = null, $limit = 10, $offset = 0)
    {
        $queryBuilder = $this->createQueryBuilderFromCriteria($criteria);

        if (is_array($orderBy)) {
            $queryBuilder->sort($orderBy);
        }

        $queryBuilder->skip($offset);
        $queryBuilder->limit($limit);

        $pageClassName = $this->getPageClassName();

        return new $pageClassName(
            $queryBuilder->getQuery()->execute(),
            ($offset / $limit) + 1,
            $limit,
            $this->countBy($criteria)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param array|Builder $criteria
     *
     * @return int
     */
    public function countBy($criteria)
    {
        return (int) $this->createQueryBuilderFromCriteria($criteria)
            ->refresh()
            ->getQuery()
            ->execute()
            ->count();
    }

    /**
     * Create query builder based on provided simple criteria.
     *
     * @param array|Builder $criteria
     *
     * @throws \InvalidArgumentException
     *
     * @return Builder
     */
    protected function createQueryBuilderFromCriteria($criteria)
    {
        if ($criteria instanceof Builder) {
            return $criteria;
        } elseif (!is_array($criteria)) {
            throw new \InvalidArgumentException(sprintf(
                'Criteria must be an array of query fields or a %s',
                Builder::class
            ));
        }

        $queryBuilder = $this->createQueryBuilder();

        /** @var array $criteria */
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $queryBuilder->addAnd($queryBuilder->expr()->field($field)->in($value));
            } else {
                $queryBuilder->addAnd($queryBuilder->expr()->field($field)->equals($value));
            }
        }

        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \BadMethodCallException
     *
     * @return array|object
     */
    public function __call($method, $arguments)
    {
        if (strpos($method, 'removeBy') === 0) {
            $byField = substr($method, 8, strlen($method));
            $method = 'removeBy';
        } elseif (strpos($method, 'removeOneBy') === 0) {
            $byField = substr($method, 11, strlen($method));
            $method = 'removeOneBy';
        } else {
            // @codeCoverageIgnoreStart
            return parent::__call($method, $arguments);
            // @codeCoverageIgnoreEnd
        }

        return $this->removeByCall($method, lcfirst(Inflector::classify($byField)), $arguments);
    }
}
