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
     * @param array                                                                $orderBy
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
     * @param array      $criteria
     * @param array|null $orderBy
     * @param bool       $flush
     */
    public function removeOneBy(array $criteria, array $orderBy = null, $flush = true);

    /**
     * Remove an object.
     *
     * @param \stdClass|string $object
     * @param bool             $flush
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
