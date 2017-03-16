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
     * Manager flush.
     */
    public function flush();

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
     * Return paginated elements filtered by criteria.
     *
     * @param array|\Doctrine\ORM\QueryBuilder $criteria
     * @param array                            $orderBy
     * @param int                              $itemsPerPage
     *
     * @return \Zend\Paginator\Paginator
     */
    public function findPaginatedBy($criteria, array $orderBy = [], $itemsPerPage = 10);

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
    public function add($objects, $flush = false);

    /**
     * Remove all objects.
     *
     * @param bool $flush
     */
    public function removeAll($flush = false);

    /**
     * Remove object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeBy(array $criteria, $flush = false);

    /**
     * Remove first object filtered by a set of criteria.
     *
     * @param array $criteria
     * @param bool  $flush
     */
    public function removeOneBy(array $criteria, $flush = false);

    /**
     * Remove objects.
     *
     * @param \stdClass|\stdClass[]|string|int $objects
     * @param bool                             $flush
     */
    public function remove($objects, $flush = false);

    /**
     * Get all objects count.
     *
     * @return int
     */
    public function countAll();

    /**
     * Get object count filtered by a set of criteria.
     *
     * @param array|\Doctrine\ORM\QueryBuilder $criteria
     *
     * @return int
     */
    public function countBy($criteria);
}
