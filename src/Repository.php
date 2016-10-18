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
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName();

    /**
     * Disable event subscriber.
     *
     * @param string $subscriberClass
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
     * @param string $event
     * @param string $subscriberClass
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
     * Set page class name.
     *
     * @return string
     */
    public function getPageClassName();

    /**
     * Get page class name.
     *
     * @param string $className
     */
    public function setPageClassName($className);

    /**
     * Return paged elements filtered by criteria.
     *
     * @param array|\Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder $criteria
     * @param array|null                                                           $orderBy
     * @param int                                                                  $limit
     * @param int                                                                  $offset
     *
     * @return \Jgut\Doctrine\Repository\Pager\Page
     */
    public function findPagedBy($criteria, array $orderBy = null, $limit = 10, $offset = 0);

    /**
     * Find one object by a set of criteria or create a new one.
     *
     * @return \stdClass
     */
    public function findOneByOrCreateNew($criteria);

    /**
     * Get a new object instance.
     *
     * @return \stdClass
     */
    public function createNew();

    /**
     * Save object.
     *
     * @param \stdClass $object
     * @param bool      $flush
     */
    public function save($object, $flush = true);

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
     * Remove an object.
     *
     * @param \stdClass|array $object
     * @param bool            $flush
     */
    public function remove($object, $flush = true);

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
