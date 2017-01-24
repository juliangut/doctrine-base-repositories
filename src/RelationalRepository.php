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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as RelationalPaginator;
use Jgut\Doctrine\Repository\Paginator\RelationalAdapter;
use Jgut\Doctrine\Repository\Traits\EventsTrait;
use Jgut\Doctrine\Repository\Traits\PaginatorTrait;
use Jgut\Doctrine\Repository\Traits\RepositoryTrait;
use Zend\Paginator\Paginator;

/**
 * Relational entity repository.
 */
class RelationalRepository extends EntityRepository implements Repository
{
    use RepositoryTrait;
    use EventsTrait;
    use PaginatorTrait;

    /**
     * Class alias.
     *
     * @var string
     */
    protected $classAlias;

    /**
     * Get class alias.
     *
     * @return string
     */
    protected function getClassAlias()
    {
        if ($this->classAlias === null) {
            $className = explode('\\', $this->getClassName());

            $this->classAlias = strtoupper(end($className)[0]);
        }

        return $this->classAlias;
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
     */
    protected function getManager()
    {
        return $this->getEntityManager();
    }

    /**
     * {@inheritdoc}
     *
     * @param array|QueryBuilder $criteria
     * @param array|null         $orderBy
     * @param int                $limit
     *
     * @throws \InvalidArgumentException
     *
     * @return Paginator
     */
    public function findPaginatedBy($criteria, array $orderBy = null, $limit = 10)
    {
        $queryBuilder = $this->createQueryBuilderFromCriteria($criteria);
        $entityAlias = count($queryBuilder->getRootAliases())
            ? $queryBuilder->getRootAliases()[0]
            : $this->getClassAlias();

        if (is_array($orderBy)) {
            foreach ($orderBy as $field => $order) {
                $queryBuilder->addOrderBy($entityAlias . '.' . $field, $order);
            }
        }

        $adapter = new RelationalAdapter(new RelationalPaginator($queryBuilder->getQuery()));

        return $this->getPaginator($adapter, $limit);
    }

    /**
     * {@inheritdoc}
     *
     * @param array|QueryBuilder $criteria
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public function countBy($criteria)
    {
        $queryBuilder = $this->createQueryBuilderFromCriteria($criteria);
        $entityAlias = count($queryBuilder->getRootAliases())
            ? $queryBuilder->getRootAliases()[0]
            : $this->getClassAlias();

        return (int) $queryBuilder
            ->select('COUNT(' . $entityAlias . ')')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Create query builder based on provided simple criteria.
     *
     * @param array|QueryBuilder $criteria
     *
     * @throws \InvalidArgumentException
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilderFromCriteria($criteria)
    {
        if ($criteria instanceof QueryBuilder) {
            return $criteria;
        } elseif (!is_array($criteria)) {
            throw new \InvalidArgumentException(sprintf(
                'Criteria must be an array of query fields or a %s',
                QueryBuilder::class
            ));
        }

        $entityAlias = $this->getClassAlias();
        $queryBuilder = $this->createQueryBuilder($entityAlias);

        /* @var array $criteria */
        foreach ($criteria as $field => $value) {
            if (is_null($value)) {
                $queryBuilder->andWhere(sprintf('%s.%s IS NULL', $entityAlias, $field));
            } else {
                $parameter = sprintf('%s_%s', $field, substr(sha1($field), 0, 4));

                $queryBuilder->andWhere(sprintf('%s.%s = :%s', $entityAlias, $field, $parameter));
                $queryBuilder->setParameter($parameter, $value);
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
