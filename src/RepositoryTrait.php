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
     * Check if the object is of the proper type.
     *
     * @param object $object
     *
     * @return bool
     */
    protected function canBeManaged($object)
    {
        return is_a($object, $this->getClassName());
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
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function disableEventSubscriber($subscriberClass)
    {
        if (!is_string($subscriberClass) && !is_a($subscriberClass, EventSubscriber::class)) {
            throw new \InvalidArgumentException('subscriberClass must be a EventSubscriber');
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function disableEventListener($event, $subscriberClass)
    {
        if (!is_string($subscriberClass) && !is_a($subscriberClass, EventSubscriber::class)) {
            throw new \InvalidArgumentException('subscriberClass must be a EventSubscriber');
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
     * {@inheritdoc}
     */
    public function restoreAllEventListeners()
    {
        foreach (array_keys($this->disabledListeners) as $event) {
            $this->restoreEventListeners($event);
        }
    }

    /**
     * {@inheritdoc}
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
     *
     * @throws \InvalidArgumentException
     */
    public function save($object, $flush = true)
    {
        if (!$this->canBeManaged($object)) {
            throw new \InvalidArgumentException(sprintf('Managed object must be a %s', $this->getClassName()));
        }

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
    public function removeOneBy(array $criteria, $flush = true)
    {
        $object = $this->findOneBy($criteria);

        if ($object !== null) {
            $manager = $this->getManager();

            $manager->remove($object);

            if ($flush === true) {
                $manager->flush();
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
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
                if (!$this->canBeManaged($obj)) {
                    throw new \InvalidArgumentException(sprintf('Managed object must be a %s', $this->getClassName()));
                }

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
    protected function removeByCall($method, $fieldName, $arguments)
    {
        if (count($arguments) === 0) {
            throw new \BadMethodCallException(sprintf(
                'You need to pass a parameter to "%s"',
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
            'Invalid remove by call %s::%s (%s)',
            $this->getClassName(),
            $fieldName,
            $method . ucfirst($fieldName)
        ));
    }

    /**
     * Get class metadata.
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    abstract protected function getClassMetadata();
}
