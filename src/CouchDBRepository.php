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
use Doctrine\ODM\CouchDB\DocumentRepository;
use Jgut\Doctrine\Repository\Pagination\CouchDBAdapter;
use Jgut\Doctrine\Repository\Traits\EventsTrait;
use Jgut\Doctrine\Repository\Traits\PaginatorTrait;
use Jgut\Doctrine\Repository\Traits\RepositoryTrait;
use Zend\Paginator\Paginator;

/**
 * CouchDB document repository.
 */
class CouchDBRepository extends DocumentRepository implements Repository
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
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int        $limit
     *
     * @return Paginator
     */
    public function findPaginatedBy($criteria, array $orderBy = null, $limit = 10)
    {
        if (!is_array($criteria)) {
            $criteria = [$criteria];
        }

        $adapter = new CouchDBAdapter($this->findBy($criteria, $orderBy));

        return $this->getPaginator($adapter, $limit);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $criteria
     *
     * @return int
     */
    public function countBy($criteria)
    {
        return count($this->findBy($criteria));
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
            'findBy',
            'findOneBy',
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

        throw new \BadMethodCallException(sprintf(
            'Undefined method "%s". Method name must start with'
            . ' "findBy", "findOneBy", "findPaginatedBy", "removeBy" or "removeOneBy"!',
            $method
        ));
    }
}
