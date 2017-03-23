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

namespace Jgut\Doctrine\Repository\Tests\Stubs;

use Jgut\Doctrine\Repository\PaginatorTrait;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

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
     * @return Paginator
     */
    public function getPaginated(array $items, $limit): Paginator
    {
        return $this->getPaginator(new ArrayAdapter($items), $limit);
    }
}
