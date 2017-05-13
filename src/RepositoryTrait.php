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
 *
 * @method mixed find()
 * @method mixed findAll()
 * @method mixed findBy()
 * @method mixed findOneBy()
 */
trait RepositoryTrait
{
    protected static $supportedMethods = ['findBy', 'findOneBy', 'findPaginatedBy', 'removeBy', 'removeOneBy'];

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
        $this->autoFlush = $autoFlush;
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

        if (!$this->canBeManaged($object)) {
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
     * @param object|object[]|\Traversable $objects
     * @param bool                         $flush
     *
     * @throws \InvalidArgumentException
     */
    public function add($objects, bool $flush = false)
    {
        $this->runManagerAction($objects, 'persist', $flush);
    }

    /**
     * Remove all objects.
     *
     * @param bool $flush
     */
    public function removeAll(bool $flush = false)
    {
        $this->runManagerAction($this->findAll(), 'remove', $flush);
    }

    /**
     * Remove object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeBy(array $criteria, bool $flush = false)
    {
        $this->runManagerAction($this->findBy($criteria), 'remove', $flush);
    }

    /**
     * Remove first object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeOneBy(array $criteria, bool $flush = false)
    {
        $this->runManagerAction($this->findOneBy($criteria), 'remove', $flush);
    }

    /**
     * Remove objects.
     *
     * @param object|object[]|\Traversable|string|int $objects
     * @param bool                                    $flush
     *
     * @throws \InvalidArgumentException
     */
    public function remove($objects, bool $flush = false)
    {
        if (!is_object($objects) && !is_array($objects) && !$objects instanceof \Traversable) {
            $objects = $this->find($objects);
        }

        $this->runManagerAction($objects, 'remove', $flush);
    }

    /**
     * Refresh objects.
     *
     * @param object|object[]|\Traversable $objects
     *
     * @throws \InvalidArgumentException
     */
    public function refresh($objects)
    {
        $backupAutoFlush = $this->autoFlush;

        $this->autoFlush = false;
        $this->runManagerAction($objects, 'refresh', false);

        $this->autoFlush = $backupAutoFlush;
    }

    /**
     * Detach objects.
     *
     * @param object|object[]|\Traversable $objects
     *
     * @throws \InvalidArgumentException
     */
    public function detach($objects)
    {
        $backupAutoFlush = $this->autoFlush;

        $this->autoFlush = false;
        $this->runManagerAction($objects, 'detach', false);

        $this->autoFlush = $backupAutoFlush;
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
     * Adds support for magic methods.
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
        if (count($arguments) === 0) {
            throw new \BadMethodCallException(sprintf(
                'You need to call %s::%s with a parameter',
                $this->getClassName(),
                $method
            ));
        }

        foreach (static::$supportedMethods as $supportedMethod) {
            if (strpos($method, $supportedMethod) === 0) {
                $field = substr($method, strlen($supportedMethod));
                $method = substr($method, 0, strlen($supportedMethod));

                return $this->callSupportedMethod($method, Inflector::camelize($field), $arguments);
            }
        }

        throw new \BadMethodCallException(sprintf(
            'Undefined method "%s". Method call must start with one of "%s"!',
            $method,
            implode('", "', static::$supportedMethods)
        ));
    }

    /**
     * Internal method call.
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

        if (!$classMetadata->hasField($fieldName) && !$classMetadata->hasAssociation($fieldName)) {
            throw new \BadMethodCallException(sprintf(
                'Invalid call to %s::%s. Field "%s" does not exist',
                $this->getClassName(),
                $method,
                $fieldName
            ));
        }

        // @codeCoverageIgnoreStart
        $parameters = array_merge(
            [$fieldName => $arguments[0]],
            array_slice($arguments, 1)
        );

        return call_user_func_array([$this, $method], $parameters);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Run manager action.
     *
     * @param object|object[]|\Traversable $objects
     * @param string                       $action
     * @param bool                         $flush
     *
     * @throws \InvalidArgumentException
     */
    protected function runManagerAction($objects, string $action, bool $flush)
    {
        $manager = $this->getManager();

        if (!is_array($objects) && !$objects instanceof \Traversable) {
            $objects = array_filter([$objects]);
        }

        foreach ($objects as $object) {
            if (!$this->canBeManaged($object)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Managed object must be a %s. "%s" given',
                        $this->getClassName(),
                        is_object($object) ? get_class($object) : gettype($object)
                    )
                );
            }

            $manager->$action($object);
        }

        $this->doFlush($objects instanceof \Traversable ? iterator_to_array($objects) : $objects, $flush);
    }

    /**
     * Flush managed objects.
     *
     * @param object[] $objects
     * @param bool     $flush
     */
    protected function doFlush(array $objects, bool $flush)
    {
        if ($flush || $this->autoFlush) {
            $this->getManager()->flush($objects);
        }
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
        $managedClass = $this->getClassName();

        return $object instanceof $managedClass;
    }

    /**
     * Returns the fully qualified class name of the objects managed by the repository.
     *
     * @return string
     */
    abstract public function getClassName(): string;

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
