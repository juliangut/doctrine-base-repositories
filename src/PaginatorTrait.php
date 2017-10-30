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

use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\Paginator;

/**
 * Pagination trait.
 */
trait PaginatorTrait
{
    /**
     * Get configured paginator.
     *
     * @param AdapterInterface $adapter
     * @param int              $itemsPerPage
     *
     * @return Paginator
     */
    protected function getPaginator(AdapterInterface $adapter, int $itemsPerPage): Paginator
    {
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(max(-1, $itemsPerPage));

        return $paginator;
    }

    /**
     * Return paginated elements filtered by criteria or throw an exception if none found.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int        $itemsPerPage
     *
     * @throws \DomainException
     *
     * @return Paginator
     */
    public function findPaginatedByOrFail(array $criteria, array $orderBy = null, int $itemsPerPage = 10): Paginator
    {
        $paginator = $this->findPaginatedBy($criteria, $orderBy, $itemsPerPage);

        if ($paginator->count() === 0) {
            throw new \DomainException('FindPaginatedBy did not return any results');
        }

        return $paginator;
    }

    /**
     * Return paginated elements filtered by criteria.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int        $itemsPerPage
     *
     * @return Paginator
     */
    abstract public function findPaginatedBy($criteria, array $orderBy = null, int $itemsPerPage = 10): Paginator;
}
