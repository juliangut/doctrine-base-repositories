<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Tests\Stubs;

use Jgut\Doctrine\Repository\Traits\PaginatorTrait;
use Zend\Paginator\Adapter\ArrayAdapter;

/**
 * Paginator trait stub.
 */
class PaginatorTraitStub
{
    use PaginatorTrait;

    /**
     * Get paginated items.
     *
     * @param array $items
     * @param int   $limit
     *
     * @return \Zend\Paginator\Paginator
     */
    public function getPaginated(array $items, $limit)
    {
        return $this->getPaginator(new ArrayAdapter($items), $limit);
    }
}
