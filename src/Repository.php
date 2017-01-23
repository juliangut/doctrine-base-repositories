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

use Doctrine\Common\Persistence\ObjectRepository;

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
    public function isAutoFlush();

    /**
     * Set automatic manager flushing.
     *
     * @param bool $autoFlush
     */
    public function setAutoFlush($autoFlush = false);

    /**
     * Returns the fully qualified class name of the objects managed by the repository.
     *
     * @return string
     */
    public function getClassName();

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
    public function disableEventListeners($event);

    /**
     * Disable listener for an event.
     *
     * @param string                                  $event
     * @param \Doctrine\Common\EventSubscriber|string $subscriberClass
     */
    public function disableEventListener($event, $subscriberClass);

    /**
     * Restore all disabled listeners.
     */
    public function restoreAllEventListeners();

    /**
     * Restore disabled listeners for an event.
     *
     * @param string $event
     */
    public function restoreEventListeners($event);

    /**
     * Get page class name.
     *
     * @return string
     */
    public function getPagerClassName();

    /**
     * Set page class name.
     *
     * @param string $className
     */
    public function setPagerClassName($className);

    /**
     * Return paged elements filtered by criteria.
     *
     * @param array|\Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder $criteria
     * @param array|null                                                           $orderBy
     * @param int                                                                  $limit
     * @param int                                                                  $offset
     *
     * @return \Jgut\Doctrine\Repository\Pager\Pager
     */
    public function findPagedBy($criteria, array $orderBy = null, $limit = 10, $offset = 0);

    /**
     * Find one object by a set of criteria or create a new one.
     *
     * @param array|\Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder $criteria
     *
     * @return \stdClass
     */
    public function findOneByOrGetNew($criteria);

    /**
     * Get a new managed object instance.
     *
     * @return \stdClass
     */
    public function getNew();

    /**
     * Add objects.
     *
     * @param \stdClass|\stdClass[] $objects
     * @param bool                  $flush
     */
    public function add($objects, $flush = true);

    /**
     * Remove all objects.
     *
     * @param bool $flush
     */
    public function removeAll($flush = true);

    /**
     * Remove object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeBy(array $criteria, $flush = true);

    /**
     * Remove first object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeOneBy(array $criteria, $flush = true);

    /**
     * Remove objects.
     *
     * @param \stdClass|\stdClass[]|string|int $objects
     * @param bool                             $flush
     */
    public function remove($objects, $flush = true);

    /**
     * Get all objects count.
     *
     * @return int
     */
    public function countAll();

    /**
     * Get object count filtered by a set of criteria.
     *
     * @param array|\Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder $criteria
     *
     * @return int
     */
    public function countBy($criteria);
}
