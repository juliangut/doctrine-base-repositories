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
 * @method mixed find($id, $lockMode = null, $lockVersion = null)
 * @method mixed findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method mixed findOneBy(array $criteria, array $orderBy = null)
 * @method mixed findAll()
 */
trait RepositoryTrait
{
    /**
     * Supported magic methods.
     *
     * @var array
     */
    protected static $supportedMethods = [
        'findBy',
        'findOneBy',
        'findPaginatedBy',
        'removeBy',
        'removeOneBy',
        'countBy',
    ];

    /**
     * Methods that support exception throwing on fail.
     *
     * @var array
     */
    protected static $falibleMethods = [
        'findBy',
        'findOneBy',
        'findPaginatedBy',
    ];

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
     * Find elements or throw an exception if none found.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @throws \DomainException
     *
     * @return object[]
     */
    public function findByOrFail(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): array
    {
        $objects = $this->findBy($criteria, $orderBy, $limit, $offset);

        if (count($objects) === 0) {
            throw new \DomainException('FindBy did not return any results');
        }

        return $objects;
    }

    /**
     * Find elements or throw an exception if none found.
     *
     * @param array $criteria
     *
     * @throws \DomainException
     *
     * @return object
     */
    public function findOneByOrFail(array $criteria)
    {
        $object = $this->findOneBy($criteria);

        if ($object === null) {
            throw new \DomainException('FindOneBy did not return any results');
        }

        return $object;
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
        $object = call_user_func($this->getObjectFactory());

        if (!$this->canBeManaged($object)) {
            throw new \RuntimeException(
                sprintf(
                    'Object factory must return an instance of %s. "%s" returned',
                    $this->getClassName(),
                    is_object($object) ? get_class($object) : gettype($object)
                )
            );
        }

        return $object;
    }

    /**
     * Get object factory.
     *
     * @return callable
     */
    private function getObjectFactory(): callable
    {
        if ($this->objectFactory === null) {
            $className = $this->getClassName();

            $this->objectFactory = function () use ($className) {
                return new $className();
            };
        }

        return $this->objectFactory;
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
     * Add objects.
     *
     * @param object|iterable $objects
     * @param bool            $flush
     *
     * @throws \InvalidArgumentException
     */
    public function add($objects, bool $flush = false)
    {
        $this->runManagerAction('persist', $objects, $flush);
    }

    /**
     * Remove all objects.
     *
     * @param bool $flush
     */
    public function removeAll(bool $flush = false)
    {
        $this->runManagerAction('remove', $this->findAll(), $flush);
    }

    /**
     * Remove object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeBy(array $criteria, bool $flush = false)
    {
        $this->runManagerAction('remove', $this->findBy($criteria), $flush);
    }

    /**
     * Remove first object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeOneBy(array $criteria, bool $flush = false)
    {
        $this->runManagerAction('remove', $this->findOneBy($criteria), $flush);
    }

    /**
     * Remove objects.
     *
     * @param object|iterable|string|int $objects
     * @param bool                       $flush
     *
     * @throws \InvalidArgumentException
     */
    public function remove($objects, bool $flush = false)
    {
        if (!is_object($objects) && !is_iterable($objects)) {
            $objects = $this->find($objects);
        }

        $this->runManagerAction('remove', $objects, $flush);
    }

    /**
     * Refresh objects.
     *
     * @param object|iterable $objects
     *
     * @throws \InvalidArgumentException
     */
    public function refresh($objects)
    {
        $backupAutoFlush = $this->autoFlush;

        $this->autoFlush = false;
        $this->runManagerAction('refresh', $objects, false);

        $this->autoFlush = $backupAutoFlush;
    }

    /**
     * Detach objects.
     *
     * @param object|iterable $objects
     *
     * @throws \InvalidArgumentException
     */
    public function detach($objects)
    {
        $backupAutoFlush = $this->autoFlush;

        $this->autoFlush = false;
        $this->runManagerAction('detach', $objects, false);

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

        $baseMethod = $this->getSupportedMethod($method);

        if (in_array($baseMethod, static::$falibleMethods) && preg_match('/OrFail$/', $method)) {
            $field = substr($method, strlen($baseMethod), -6);
            $method = $baseMethod . 'OrFail';
        } elseif ($baseMethod === 'findOneBy' && preg_match('/OrGetNew$/', $method)) {
            $field = substr($method, strlen($baseMethod), -8);
            $method = 'findOneByOrGetNew';
        } else {
            $field = substr($method, strlen($baseMethod));
            $method = $baseMethod;
        }

        return $this->callSupportedMethod($method, Inflector::camelize($field), $arguments);
    }

    /**
     * Get supported magic method.
     *
     * @param string $method
     *
     * @throws \BadMethodCallException
     *
     * @return string
     */
    private function getSupportedMethod(string $method): string
    {
        foreach (static::$supportedMethods as $supportedMethod) {
            if (strpos($method, $supportedMethod) === 0) {
                return $supportedMethod;
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
     * @param string          $action
     * @param object|iterable $objects
     * @param bool            $flush
     *
     * @throws \InvalidArgumentException
     */
    protected function runManagerAction(string $action, $objects, bool $flush)
    {
        $manager = $this->getManager();

        if (!is_iterable($objects)) {
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

        // @codeCoverageIgnoreStart
        if ($objects instanceof \Traversable) {
            $objects = iterator_to_array($objects);
        }
        // @codeCoverageIgnoreEnd

        $this->flushObjects($objects, $flush);
    }

    /**
     * Flush managed objects.
     *
     * @param array $objects
     * @param bool  $flush
     */
    protected function flushObjects(array $objects, bool $flush)
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
