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
     * Disable event subscriber.
     *
     * @param \Doctrine\Common\EventSubscriber|string $subscriberClass
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
     * Restore disabled event subscribers.
     */
    public function restoreEventSubscribers()
    {
        foreach ($this->disabledSubscribers as $subscriber) {
            $this->getEventManager()->addEventSubscriber($subscriber);
        }

        $this->disabledSubscribers = [];
    }

    /**
     * Disable all listeners for an event.
     *
     * @param string $event
     */
    public function disableEventListeners(string $event)
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
     * Disable listener for an event.
     *
     * @param string                 $event
     * @param string|EventSubscriber $subscriberClass
     *
     * @throws \InvalidArgumentException
     */
    public function disableEventListener(string $event, $subscriberClass)
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
     * Restore all disabled listeners.
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
     * Restore disabled listeners for an event.
     *
     * @param string $event
     */
    public function restoreEventListeners(string $event)
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
    protected function getSubscriberClassName($subscriberClass): string
    {
        if (is_object($subscriberClass) && in_array(EventSubscriber::class, class_implements($subscriberClass))) {
            return get_class($subscriberClass);
        }

        if (!is_string($subscriberClass)
            || !class_exists($subscriberClass)
            || !in_array(EventSubscriber::class, class_implements($subscriberClass))
        ) {
            throw new \InvalidArgumentException('subscriberClass must be an EventSubscriber');
        }

        return $subscriberClass;
    }

    /**
     * Get registered events.
     *
     * @return array
     */
    public function getRegisteredEvents(): array
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
    protected function getEventListeners($event = null): array
    {
        $eventManager = $this->getEventManager();

        return $event !== null && !$eventManager->hasListeners($event) ? [] : $eventManager->getListeners($event);
    }

    /**
     * Get event manager.
     *
     * @return EventManager
     */
    protected function getEventManager(): EventManager
    {
        return $this->getManager()->getEventManager();
    }

    /**
     * Get object manager.
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    abstract protected function getManager();
}
