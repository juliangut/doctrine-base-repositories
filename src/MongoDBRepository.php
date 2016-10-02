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
use Doctrine\ODM\MongoDB\DocumentRepository;

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
     */
    public function countAll()
    {
        return (int) $this->createQueryBuilder()->refresh()->getQuery()->execute()->count();
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

        if (count($arguments) === 0) {
            throw new \BadMethodCallException(sprintf('You need to pass a parameter to "%s"', $method . $byField));
        }

        $fieldName = lcfirst(Inflector::classify($byField));

        if ($this->getClassMetadata()->hasField($fieldName) || $this->getClassMetadata()->hasAssociation($fieldName)) {
            // @codeCoverageIgnoreStart
            $parameters = array_merge(
                [$fieldName => $arguments[0]],
                array_slice($arguments, 1)
            );

            return call_user_func_array([$this, $method], $parameters);
            // @codeCoverageIgnoreEnd
        }

        throw new \BadMethodCallException(sprintf(
            'Invalid remove by call %s::%s (%s)',
            $this->getClassName(),
            $fieldName,
            $method . $byField
        ));
    }
}
