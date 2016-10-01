<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository;

/**
 * Repository interface.
 */
interface RepositoryInterface
{
    /**
     * Find one object by a set of criteria or create a new one.
     *
     * @return object
     */
    public function findOneByOrCreateNew($criteria);

    /**
     * Get a new object instance.
     *
     * @return object
     */
    public function createNew();

    /**
     * Save object.
     *
     * @param object $object
     * @param bool   $flush
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
     * @param object|string $object
     * @param bool          $flush
     */
    public function remove($object, $flush = true);

    /**
     * Finds objects by a set of criteria and limited by pagination.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int        $page
     * @param int        $pageSize
     *
     * @throws \OutOfBoundsException
     *
     * @return array
     */
    public function paginatedFindBy(array $criteria, array $orderBy = null, $page = 1, $pageSize = 10);

    /**
     * Get all objects count.
     *
     * @return int
     */
    public function countAll();

    /**
     * Get object count filtered by a set of criteria.
     *
     * @param array $criteria
     *
     * @return int
     */
    public function count(array $criteria);
}
