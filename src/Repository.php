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

use Doctrine\Common\Persistence\ObjectRepository;
use Zend\Paginator\Paginator;

/**
 * Repository interface.
 */
interface Repository extends ObjectRepository
{
    /**
     * Get automatic manager flushing.
     *
     * @return bool
     */
    public function isAutoFlush(): bool;

    /**
     * Set automatic manager flushing.
     *
     * @param bool $autoFlush
     */
    public function setAutoFlush(bool $autoFlush = false);

    /**
     * Manager flush.
     */
    public function flush();

    /**
     * Set object factory.
     *
     * @param callable $objectFactory
     */
    public function setObjectFactory(callable $objectFactory);

    /**
     * Returns the fully qualified class name of the objects managed by the repository.
     *
     * @return string
     */
    public function getClassName(): string;

    /**
     * Disable event subscriber.
     *
     * @param \Doctrine\Common\EventSubscriber|string $subscriberClass
     */
    public function disableEventSubscriber($subscriberClass);

    /**
     * Restore disabled event subscribers.
     */
    public function restoreEventSubscribers();

    /**
     * Disable all listeners for an event.
     *
     * @param string $event
     */
    public function disableEventListeners(string $event);

    /**
     * Disable listener for an event.
     *
     * @param string                                  $event
     * @param string|\Doctrine\Common\EventSubscriber $subscriberClass
     */
    public function disableEventListener(string $event, $subscriberClass);

    /**
     * Restore all disabled listeners.
     */
    public function restoreAllEventListeners();

    /**
     * Restore disabled listeners for an event.
     *
     * @param string $event
     */
    public function restoreEventListeners(string $event);

    /**
     * Get registered events.
     *
     * @return array
     */
    public function getRegisteredEvents(): array;

    /**
     * Disable all filters.
     */
    public function disableFilters();

    /**
     * Disable filter.
     *
     * @param string $filter
     */
    public function disableFilter(string $filter);

    /**
     * Restore all disabled filters.
     */
    public function restoreFilters();

    /**
     * Restore disabled filter.
     *
     * @param string $filter
     */
    public function restoreFilter(string $filter);

    /**
     * Return paginated elements filtered by criteria.
     *
     * @param array $criteria
     * @param array $orderBy
     * @param int   $itemsPerPage
     *
     * @return Paginator
     */
    public function findPaginatedBy($criteria, array $orderBy = [], int $itemsPerPage = 10): Paginator;

    /**
     * Find one object by a set of criteria or create a new one.
     *
     * @param array $criteria
     *
     * @return object
     */
    public function findOneByOrGetNew(array $criteria);

    /**
     * Get a new managed object instance.
     *
     * @return object
     */
    public function getNew();

    /**
     * Add objects.
     *
     * @param object|iterable $objects
     * @param bool            $flush
     */
    public function add($objects, bool $flush = false);

    /**
     * Remove all objects.
     *
     * @param bool $flush
     */
    public function removeAll(bool $flush = false);

    /**
     * Remove object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeBy(array $criteria, bool $flush = false);

    /**
     * Remove first object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeOneBy(array $criteria, bool $flush = false);

    /**
     * Remove objects.
     *
     * @param object|iterable|string|int $objects
     * @param bool                       $flush
     */
    public function remove($objects, bool $flush = false);

    /**
     * Refresh objects.
     *
     * @param object|iterable $objects
     */
    public function refresh($objects);

    /**
     * Detach objects.
     *
     * @param object|iterable $objects
     */
    public function detach($objects);

    /**
     * Get all objects count.
     *
     * @return int
     */
    public function countAll(): int;

    /**
     * Get object count filtered by a set of criteria.
     *
     * @param mixed $criteria
     *
     * @return int
     */
    public function countBy($criteria): int;
}
