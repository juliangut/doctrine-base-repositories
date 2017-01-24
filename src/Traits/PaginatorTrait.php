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
    protected function getPaginator(AdapterInterface $adapter, $itemsPerPage)
    {
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(max(0, (int) $itemsPerPage));

        return $paginator;
    }
}
