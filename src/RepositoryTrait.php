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

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Jgut\Doctrine\Repository\Pager\DefaultPage;
use Jgut\Doctrine\Repository\Pager\Page;

/**
 * Repository trait.
 */
trait RepositoryTrait
{
    /**
     * List of disabled event subscribers.
     *
     * @var EventSubscriber[]
     */
    protected $disabledSubscribers = [];

    /**
     * List of disabled event listeners.
     *
     * @var EventSubscriber[]
     */
    protected $disabledListeners = [];

    /**
     * Page class name.
     *
     * @var string
     */
    protected $pageClassName = DefaultPage::class;

    /**
     * Get object manager.
     *
     * @return \Doctrine\ORM\EntityManager|\Doctrine\ODM\MongoDB\DocumentManager|\Doctrine\ODM\CouchDB\DocumentManager
     */
    abstract protected function getManager();

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    abstract public function getClassName();

    /**
     * Disable event subscriber.
     *
     * @param string $subscriberClass
     *
     * @throws \InvalidArgumentException
     */
    public function disableEventSubscriber($subscriberClass)
    {
        if (!is_string($subscriberClass) && !is_a($subscriberClass, EventSubscriber::class)) {
            throw new \InvalidArgumentException('subscriberClass must be class implementing EventSubscriber');
        }

        /* @var \Doctrine\Common\EventManager $eventManager */
        $eventManager = $this->getManager()->getEventManager();

        foreach ($eventManager->getListeners() as $subscribers) {
            $found = false;
            while (!$found && $subscriber = array_shift($subscribers)) {
                if ($subscriber instanceof $subscriberClass) {
                    $this->disabledSubscribers[] = $subscriber;

                    $eventManager->removeEventSubscriber($subscriber);

                    $found = true;
                }
            }

            if ($found) {
                break;
            }
        }
    }

    /**
     * Restore disabled event subscribers.
     */
    public function restoreEventSubscribers()
    {
        /* @var \Doctrine\Common\EventManager $eventManager */
        $eventManager = $this->getManager()->getEventManager();

        foreach ($this->disabledSubscribers as $subscriber) {
            $eventManager->addEventSubscriber($subscriber);
        }

        $this->disabledSubscribers = [];
    }

    /**
     * Disable all listeners for an event.
     *
     * @param string $event
     */
    public function disableEventListeners($event)
    {
        /* @var \Doctrine\Common\EventManager $eventManager */
        $eventManager = $this->getManager()->getEventManager();

        foreach ($this->getEventListeners($eventManager, $event) as $listener) {
            $this->disabledListeners[$event][] = $listener;

            $eventManager->removeEventListener($event, $listener);
        }
    }

    /**
     * Disable listener for an event.
     *
     * @param string $event
     * @param string $subscriberClass
     *
     * @throws \InvalidArgumentException
     */
    public function disableEventListener($event, $subscriberClass)
    {
        if (!is_string($subscriberClass) && !is_a($subscriberClass, EventSubscriber::class)) {
            throw new \InvalidArgumentException('subscriberClass must be class implementing EventSubscriber');
        }

        /* @var \Doctrine\Common\EventManager $eventManager */
        $eventManager = $this->getManager()->getEventManager();

        foreach ($this->getEventListeners($eventManager, $event) as $listener) {
            if ($listener instanceof $subscriberClass) {
                $this->disabledListeners[$event][] = $listener;

                $eventManager->removeEventListener($event, $listener);
                break;
            }
        }
    }

    /**
     * Get listeners for an event.
     *
     * @param EventManager $eventManager
     * @param string       $event
     *
     * @return \Doctrine\Common\EventSubscriber[]
     */
    protected function getEventListeners(EventManager $eventManager, $event)
    {
        if (!$eventManager->hasListeners($event)) {
            return [];
        }

        if (!array_key_exists($event, $this->disabledListeners)) {
            $this->disabledListeners[$event] = [];
        }

        return $eventManager->getListeners($event);
    }

    /**
     * Restore all disabled listeners.
     */
    public function restoreAllEventListeners()
    {
        foreach (array_keys($this->disabledListeners) as $event) {
            $this->restoreEventListeners($event);
        }
    }

    /**
     * Restore disabled listeners for an event.
     *
     * @param string $event
     */
    public function restoreEventListeners($event)
    {
        if (!array_key_exists($event, $this->disabledListeners)) {
            return;
        }

        /* @var \Doctrine\Common\EventManager $eventManager */
        $eventManager = $this->getManager()->getEventManager();

        /* @var EventSubscriber[] $listeners */
        $listeners = $this->disabledListeners[$event];

        foreach ($listeners as $listener) {
            $eventManager->addEventListener($event, $listener);
        }

        unset($this->disabledListeners[$event]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageClassName()
    {
        return $this->pageClassName;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function setPageClassName($className)
    {
        $reflectionClass = new \ReflectionClass($className);

        if (!$reflectionClass->implementsInterface(Page::class)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid page class "%s". It must be a %s.',
                $className,
                Page::class
            ));
        }

        $this->pageClassName = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByOrCreateNew($criteria)
    {
        $object = $this->findOneBy($criteria);

        if ($object === null) {
            $object = $this->createNew();
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function createNew()
    {
        $className = $this->getClassName();

        return new $className;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, $flush = true)
    {
        $manager = $this->getManager();

        $manager->persist($object);

        if ($flush === true) {
            $manager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll($flush = true)
    {
        $manager = $this->getManager();

        foreach ($this->findAll() as $object) {
            $manager->remove($object);
        }

        if ($flush === true) {
            $manager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeBy(array $criteria, $flush = true)
    {
        $manager = $this->getManager();

        foreach ($this->findBy($criteria) as $object) {
            $manager->remove($object);
        }

        if ($flush === true) {
            $manager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeOneBy(array $criteria, array $orderBy = null, $flush = true)
    {
        $object = $this->findOneBy($criteria, $orderBy);

        if ($object !== null) {
            $this->remove($object, $flush);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, $flush = true)
    {
        $manager = $this->getManager();

        if (!is_object($object)) {
            $object = $this->find($object);
        }

        if ($object !== null) {
            if (!is_array($object)) {
                $object = [$object];
            }

            foreach ($object as $obj) {
                $manager->remove($obj);
            }

            if ($flush === true) {
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
    protected function removeByCall($method, $fieldName, $arguments)
    {
        if (count($arguments) === 0) {
            throw new \BadMethodCallException(sprintf(
                'You need to pass a parameter to "%s"',
                $method . ucfirst($fieldName)
            ));
        }

        /** @var \Doctrine\Common\Persistence\Mapping\ClassMetadata $classMetadata */
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
            'Invalid remove by call %s::%s (%s)',
            $this->getClassName(),
            $fieldName,
            $method . ucfirst($fieldName)
        ));
    }
}
