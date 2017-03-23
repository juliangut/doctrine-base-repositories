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
        $paginator->setItemCountPerPage(max(-1, (int) $itemsPerPage));

        return $paginator;
    }
}
