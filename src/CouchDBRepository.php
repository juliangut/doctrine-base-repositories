<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license BSD-3-Clause
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
     */
    public function countAll()
    {
        return $this->countBy([]);
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
