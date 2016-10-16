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

/**
 * CouchDB document repository.
 */
class CouchDBRepository extends DocumentRepository implements Repository
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
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int        $limit
     * @param int        $offset
     *
     * @return \Jgut\Doctrine\Repository\Pager\Page
     */
    public function findPagedBy($criteria, array $orderBy = null, $limit = 10, $offset = 0)
    {
        $pageClassName = $this->getPageClassName();

        if (!is_array($criteria)) {
            $criteria = [$criteria];
        }

        return new $pageClassName(
            $this->findBy($criteria, $orderBy, $limit, $offset),
            ($offset / $limit) + 1,
            $limit,
            $this->countBy($criteria)
        );
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
     * Adds support for magic finders.
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
        if (strpos($method, 'removeBy') === 0) {
            $byField = substr($method, 8, strlen($method));
            $method = 'removeBy';
        } elseif (strpos($method, 'removeOneBy') === 0) {
            $byField = substr($method, 11, strlen($method));
            $method = 'removeOneBy';
        } else {
            throw new \BadMethodCallException(sprintf(
                'Undefined method: "%s". The method name must start with "findBy", "findOneBy"!',
                $method
            ));
        }

        return $this->removeByCall($method, lcfirst(Inflector::classify($byField)), $arguments);
    }
}
