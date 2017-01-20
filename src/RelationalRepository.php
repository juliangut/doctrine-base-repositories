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

/**
 * Relational entity repository.
 */
class RelationalRepository extends EntityRepository implements Repository
{
    use RepositoryTrait;

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
     * @param int                $offset
     *
     * @throws \InvalidArgumentException
     *
     * @return \Jgut\Doctrine\Repository\Pager\Page
     */
    public function findPagedBy($criteria, array $orderBy = null, $limit = 10, $offset = 0)
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

        $queryBuilder->setFirstResult($offset);
        $queryBuilder->setMaxResults($limit);

        $pageClassName = $this->getPageClassName();

        return new $pageClassName(
            $queryBuilder->getQuery()->getResult(),
            ($offset / $limit) + 1,
            $limit,
            $this->countBy($criteria)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param array|QueryBuilder $criteria
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
