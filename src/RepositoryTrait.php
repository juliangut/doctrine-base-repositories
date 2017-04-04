<?php

/*
 * doctrine-base-repositories (https://github.com/juliangut/doctrine-base-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-base-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Doctrine\Repository;

use Doctrine\Common\Util\Inflector;

/**
 * Repository trait.
 */
trait RepositoryTrait
{
    /**
     * Auto flush changes.
     *
     * @var bool
     */
    protected $autoFlush = false;

    /**
     * New object factory.
     *
     * @var callable
     */
    protected $objectFactory;

    /**
     * Get automatic manager flushing.
     *
     * @return bool
     */
    public function isAutoFlush(): bool
    {
        return $this->autoFlush;
    }

    /**
     * Set automatic manager flushing.
     *
     * @param bool $autoFlush
     */
    public function setAutoFlush(bool $autoFlush = true)
    {
        $this->autoFlush = $autoFlush === true;
    }

    /**
     * Manager flush.
     */
    public function flush()
    {
        $this->getManager()->flush();
    }

    /**
     * Set object factory.
     *
     * @param callable $objectFactory
     */
    public function setObjectFactory(callable $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    /**
     * Find one object by a set of criteria or create a new one.
     *
     * @param array $criteria
     *
     * @throws \RuntimeException
     *
     * @return object
     */
    public function findOneByOrGetNew(array $criteria)
    {
        $object = $this->findOneBy($criteria);

        if ($object === null) {
            $object = $this->getNew();
        }

        return $object;
    }

    /**
     * Get a new managed object instance.
     *
     * @throws \RuntimeException
     *
     * @return object
     */
    public function getNew()
    {
        $className = $this->getClassName();

        if ($this->objectFactory === null) {
            return new $className();
        }

        $object = call_user_func($this->objectFactory);

        if (!is_object($object) || !is_a($object, $className)) {
            throw new \RuntimeException(
                sprintf(
                    'Object factory must return an instance of %s. "%s" returned',
                    $className,
                    is_object($object) ? get_class($object) : gettype($object)
                )
            );
        }

        return $object;
    }

    /**
     * Add objects.
     *
     * @param \stdClass|\stdClass[] $objects
     * @param bool                  $flush
     *
     * @throws \InvalidArgumentException
     */
    public function add($objects, bool $flush = false)
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
     * Remove all objects.
     *
     * @param bool $flush
     */
    public function removeAll(bool $flush = false)
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
     * Remove object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeBy(array $criteria, bool $flush = false)
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
     * Remove first object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeOneBy(array $criteria, bool $flush = false)
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
     * Remove objects.
     *
     * @param object|object[]|string|int $objects
     * @param bool                       $flush
     *
     * @throws \InvalidArgumentException
     */
    public function remove($objects, bool $flush = false)
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
     * Get all objects count.
     *
     * @return int
     */
    public function countAll(): int
    {
        return $this->countBy([]);
    }

    /**
     * Get object count filtered by a set of criteria.
     *
     * @param mixed $criteria
     *
     * @return int
     */
    abstract public function countBy($criteria): int;

    /**
     * Adds support for magic finders and removers.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        static $supportedMethods = ['findBy', 'findOneBy', 'findPaginatedBy', 'removeBy', 'removeOneBy'];

        if (count($arguments) === 0) {
            throw new \BadMethodCallException(sprintf(
                'You need to pass a parameter to %s::%s',
                $this->getClassName(),
                $method
            ));
        }

        foreach ($supportedMethods as $supportedMethod) {
            if (strpos($method, $supportedMethod) === 0) {
                $field = substr($method, strlen($supportedMethod));
                $method = substr($method, 0, strlen($supportedMethod));

                return $this->callSupportedMethod($method, Inflector::camelize($field), $arguments);
            }
        }

        throw new \BadMethodCallException(sprintf(
            'Undefined method "%s". Method name must start with one of "%s"!',
            $method,
            implode('", "', $supportedMethods)
        ));
    }

    /**
     * Internal remove magic finder.
     *
     * @param string $method
     * @param string $fieldName
     * @param array  $arguments
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    protected function callSupportedMethod(string $method, string $fieldName, array $arguments)
    {
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
    protected function canBeManaged($object): bool
    {
        return is_object($object) && is_a($object, $this->getClassName());
    }

    /**
     * Get object manager.
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    abstract protected function getManager();

    /**
     * Get class metadata.
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    abstract protected function getClassMetadata();
}
