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

/**
 * Relational entity repository.
 */
class RelationalRepository extends EntityRepository implements Repository
{
    use RepositoryTrait;

    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return $this->getEntityManager();
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
        return (int) $this->createQueryBuilder('E')->select('COUNT(E)')->getQuery()->getSingleScalarResult();
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
