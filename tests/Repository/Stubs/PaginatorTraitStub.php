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
     * @var EntityStub[]
     */
    protected $entities;

    /**
     * PaginatorTraitStub constructor.
     *
     * @param array $entities
     */
    public function __construct(array $entities = [])
    {
        $this->entities = $entities;
    }

    /**
     * {@inheritdoc}
     */
    public function findPaginatedBy($criteria, array $orderBy = null, int $itemsPerPage = 10): Paginator
    {
        return $this->getPaginator(new ArrayAdapter($this->entities), $itemsPerPage);
    }
}
