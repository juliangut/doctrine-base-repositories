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
     * Get object manager.
     *
     * @return \Doctrine\ORM\EntityManager|\Doctrine\ODM\MongoDB\DocumentManager|\Doctrine\ODM\CouchDB\DocumentManager
     */
    abstract protected function getManager();
}
