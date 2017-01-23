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

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;

/**
 * Events trait.
 */
trait EventsTrait
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
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function disableEventSubscriber($subscriberClass)
    {
        $subscriberClass = $this->getSubscriberClassName($subscriberClass);

        /* @var EventManager $eventManager */
        $eventManager = $this->getManager()->getEventManager();

        /* @var EventSubscriber[] $subscribers */
        foreach ($this->getEventListeners($eventManager) as $subscribers) {
            while ($subscriber = array_shift($subscribers)) {
                if ($subscriber instanceof $subscriberClass) {
                    $this->disabledSubscribers[] = $subscriber;

                    $eventManager->removeEventSubscriber($subscriber);

                    return;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restoreEventSubscribers()
    {
        /* @var EventManager $eventManager */
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
        /* @var EventManager $eventManager */
        $eventManager = $this->getManager()->getEventManager();

        if (!array_key_exists($event, $this->disabledListeners)) {
            $this->disabledListeners[$event] = [];
        }

        foreach ($this->getEventListeners($eventManager, $event) as $listener) {
            $eventManager->removeEventListener($event, $listener);

            $this->disabledListeners[$event][] = $listener;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function disableEventListener($event, $subscriberClass)
    {
        $subscriberClass = $this->getSubscriberClassName($subscriberClass);

        if (!array_key_exists($event, $this->disabledListeners)) {
            $this->disabledListeners[$event] = [];
        }

        /* @var EventManager $eventManager */
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
        if (!array_key_exists($event, $this->disabledListeners) || empty($this->disabledListeners[$event])) {
            return;
        }

        /* @var EventManager $eventManager */
        $eventManager = $this->getManager()->getEventManager();

        /* @var EventSubscriber[] $listeners */
        $listeners = $this->disabledListeners[$event];

        foreach ($listeners as $listener) {
            $eventManager->addEventListener($event, $listener);
        }

        $this->disabledListeners[$event] = [];
    }

    /**
     * Get subscriber class name.
     *
     * @param string|EventSubscriber $subscriberClass
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function getSubscriberClassName($subscriberClass)
    {
        if (is_object($subscriberClass) && is_a($subscriberClass, EventSubscriber::class)) {
            return get_class($subscriberClass);
        }

        if (!is_string($subscriberClass) || !in_array(EventSubscriber::class, class_implements($subscriberClass))) {
            throw new \InvalidArgumentException('subscriberClass must be an EventSubscriber');
        }

        return $subscriberClass;
    }

    /**
     * Get event listeners.
     *
     * @param EventManager $eventManager
     * @param string|null  $event
     *
     * @return EventSubscriber[]
     */
    protected function getEventListeners(EventManager $eventManager, $event = null)
    {
        return $event !== null && !$eventManager->hasListeners($event) ? [] : $eventManager->getListeners($event);
    }

    /**
     * Get object manager.
     *
     * @return \Doctrine\ORM\EntityManager|\Doctrine\ODM\MongoDB\DocumentManager|\Doctrine\ODM\CouchDB\DocumentManager
     */
    abstract protected function getManager();
}
