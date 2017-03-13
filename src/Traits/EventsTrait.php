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

        /* @var EventSubscriber[] $subscribers */
        foreach ($this->getEventListeners() as $subscribers) {
            while ($subscriber = array_shift($subscribers)) {
                if ($subscriber instanceof $subscriberClass) {
                    $this->disabledSubscribers[] = $subscriber;

                    $this->getEventManager()->removeEventSubscriber($subscriber);

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
        foreach ($this->disabledSubscribers as $subscriber) {
            $this->getEventManager()->addEventSubscriber($subscriber);
        }

        $this->disabledSubscribers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function disableEventListeners($event)
    {
        if (!array_key_exists($event, $this->disabledListeners)) {
            $this->disabledListeners[$event] = [];
        }

        foreach ($this->getEventListeners($event) as $listener) {
            $this->getEventManager()->removeEventListener($event, $listener);

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

        foreach ($this->getEventListeners($event) as $listener) {
            if ($listener instanceof $subscriberClass) {
                $this->disabledListeners[$event][] = $listener;

                $this->getEventManager()->removeEventListener($event, $listener);
                break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restoreAllEventListeners()
    {
        foreach ($this->disabledListeners as $event => $listeners) {
            /* @var EventSubscriber[] $listeners */
            foreach ($listeners as $listener) {
                $this->getEventManager()->addEventListener($event, $listener);
            }

            $this->disabledListeners[$event] = [];
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

        /* @var EventSubscriber[] $listeners */
        $listeners = $this->disabledListeners[$event];

        foreach ($listeners as $listener) {
            $this->getEventManager()->addEventListener($event, $listener);
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
     * Get registered events.
     *
     * @return array
     */
    public function getRegisteredEvents()
    {
        return array_keys($this->getEventManager()->getListeners());
    }

    /**
     * Get event listeners.
     *
     * @param string|null $event
     *
     * @return EventSubscriber[]
     */
    protected function getEventListeners($event = null)
    {
        $eventManager = $this->getEventManager();

        return $event !== null && !$eventManager->hasListeners($event) ? [] : $eventManager->getListeners($event);
    }

    /**
     * Get event manager.
     *
     * @return EventManager
     */
    protected function getEventManager()
    {
        return $this->getManager()->getEventManager();
    }
}
