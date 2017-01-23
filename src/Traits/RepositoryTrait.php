<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Traits;

/**
 * Repository trait.
 */
trait RepositoryTrait
{
    protected $autoFlush = false;

    /**
     * {@inheritdoc}
     */
    public function isAutoFlush()
    {
        return $this->autoFlush;
    }

    /**
     * {@inheritdoc}
     */
    public function setAutoFlush($autoFlush = true)
    {
        $this->autoFlush = $autoFlush === true;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByOrGetNew($criteria)
    {
        $object = $this->findOneBy($criteria);

        if ($object === null) {
            $object = $this->getNew();
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function getNew()
    {
        $className = $this->getClassName();

        return new $className;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function add($objects, $flush = false)
    {
        if (!is_array($objects)) {
            $objects = [$objects];
        }

        $manager = $this->getManager();

        foreach ($objects as $object) {
            if (!$this->canBeManaged($object)) {
                throw new \InvalidArgumentException(sprintf('Managed object must be a %s', $this->getClassName()));
            }

            $manager->persist($object);
        }

        if ($flush === true || $this->autoFlush === true) {
            $manager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll($flush = false)
    {
        $manager = $this->getManager();

        foreach ($this->findAll() as $object) {
            $manager->remove($object);
        }

        if ($flush === true || $this->autoFlush === true) {
            $manager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeBy(array $criteria, $flush = false)
    {
        $manager = $this->getManager();

        foreach ($this->findBy($criteria) as $object) {
            $manager->remove($object);
        }

        if ($flush === true || $this->autoFlush === true) {
            $manager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeOneBy(array $criteria, $flush = false)
    {
        $object = $this->findOneBy($criteria);

        if ($object !== null) {
            $manager = $this->getManager();

            $manager->remove($object);

            if ($flush === true || $this->autoFlush === true) {
                $manager->flush();
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function remove($objects, $flush = false)
    {
        $manager = $this->getManager();

        if (!is_object($objects) && !is_array($objects)) {
            $objects = $this->find($objects);
        }

        if ($objects !== null) {
            if (!is_array($objects)) {
                $objects = [$objects];
            }

            foreach ($objects as $object) {
                if (!$this->canBeManaged($object)) {
                    throw new \InvalidArgumentException(sprintf('Managed object must be a %s', $this->getClassName()));
                }

                $manager->remove($object);
            }

            if ($flush === true || $this->autoFlush === true) {
                $manager->flush();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        return $this->countBy([]);
    }

    /**
     * Get object count filtered by a set of criteria.
     *
     * @param array|\Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder $criteria
     *
     * @return int
     */
    abstract public function countBy($criteria);

    /**
     * Internal remove magic finder.
     *
     * @param string $method
     * @param string $fieldName
     * @param array  $arguments
     *
     * @throws \BadMethodCallException
     *
     * @return array|object
     */
    protected function magicByCall($method, $fieldName, $arguments)
    {
        if (count($arguments) === 0) {
            throw new \BadMethodCallException(sprintf(
                'You need to pass a parameter to %s::%s',
                $this->getClassName(),
                $method . ucfirst($fieldName)
            ));
        }

        $classMetadata = $this->getClassMetadata();

        if ($classMetadata->hasField($fieldName) || $classMetadata->hasAssociation($fieldName)) {
            // @codeCoverageIgnoreStart
            $parameters = array_merge(
                [$fieldName => $arguments[0]],
                array_slice($arguments, 1)
            );

            return call_user_func_array([$this, $method], $parameters);
            // @codeCoverageIgnoreEnd
        }

        throw new \BadMethodCallException(sprintf(
            'Invalid call to %s::%s. Field "%s" does not exist',
            $this->getClassName(),
            $method,
            $fieldName
        ));
    }

    /**
     * Check if the object is of the proper type.
     *
     * @param object $object
     *
     * @return bool
     */
    protected function canBeManaged($object)
    {
        return is_object($object) && is_a($object, $this->getClassName());
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getClassName();

    /**
     * Get object manager.
     *
     * @return \Doctrine\ORM\EntityManager|\Doctrine\ODM\MongoDB\DocumentManager|\Doctrine\ODM\CouchDB\DocumentManager
     */
    abstract protected function getManager();

    /**
     * Get class metadata.
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    abstract protected function getClassMetadata();
}
