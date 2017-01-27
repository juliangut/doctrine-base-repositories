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
use Jgut\Doctrine\Repository\Pagination\MongoDBAdapter;
use Jgut\Doctrine\Repository\Traits\EventsTrait;
use Jgut\Doctrine\Repository\Traits\PaginatorTrait;
use Jgut\Doctrine\Repository\Traits\RepositoryTrait;

/**
 * MongoDB document repository.
 */
class MongoDBRepository extends DocumentRepository implements Repository
{
    use RepositoryTrait;
    use EventsTrait;
    use PaginatorTrait;

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return ClassUtils::getRealClass(parent::getClassName());
    }

    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return $this->getDocumentManager();
    }

    /**
     * {@inheritdoc}
     *
     * @param array|Builder $criteria
     * @param array|null    $orderBy
     * @param int           $itemsPerPage
     *
     * @throws \InvalidArgumentException
     *
     * @return \Zend\Paginator\Paginator
     */
    public function findPaginatedBy($criteria, array $orderBy = null, $itemsPerPage = 10)
    {
        $queryBuilder = $this->createQueryBuilderFromCriteria($criteria);

        if (is_array($orderBy)) {
            $queryBuilder->sort($orderBy);
        }

        $adapter = new MongoDBAdapter($queryBuilder->getQuery()->execute());

        return $this->getPaginator($adapter, $itemsPerPage);
    }

    /**
     * {@inheritdoc}
     *
     * @param array|Builder $criteria
     *
     * @throws \InvalidArgumentException
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

        /* @var array $criteria */
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
     * Adds support for magic finders and removers.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws \BadMethodCallException
     *
     * @return array|object
     */
    public function __call($method, $arguments)
    {
        $magicMethods = [
            'findPaginatedBy',
            'removeBy',
            'removeOneBy',
        ];

        foreach ($magicMethods as $magicMethod) {
            if (strpos($method, $magicMethod) === 0) {
                $field = substr($method, strlen($magicMethod));
                $method = substr($method, 0, strlen($magicMethod));

                return $this->magicByCall($method, lcfirst(Inflector::classify($field)), $arguments);
            }
        }

        // @codeCoverageIgnoreStart
        try {
            return parent::__call($method, $arguments);
        } catch (\BadMethodCallException $exception) {
            throw new \BadMethodCallException(sprintf(
                'Undefined method "%s". Method name must start with'
                . ' "findBy", "findOneBy", "findPaginatedBy", "removeBy" or "removeOneBy"!',
                $method
            ));
        }
        // @codeCoverageIgnoreEnd
    }
}
