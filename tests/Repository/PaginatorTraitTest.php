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

namespace Jgut\Doctrine\Repository\Tests;

use Jgut\Doctrine\Repository\Tests\Stubs\EntityStub;
use Jgut\Doctrine\Repository\Tests\Stubs\PaginatorTraitStub;
use PHPUnit\Framework\TestCase;
use Zend\Paginator\Paginator;

/**
 * Paginator trait tests.
 */
class PaginatorTraitTest extends TestCase
{
    public function testFindPaginatedByOrFail()
    {
        $entity = new EntityStub();

        $trait = new PaginatorTraitStub([$entity]);

        static::assertInstanceOf(Paginator::class, $trait->findPaginatedByOrFail([], [], 10));
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage FindPaginatedBy did not return any results
     */
    public function testFailingFindPaginatedByOrFail()
    {
        $trait = new PaginatorTraitStub();

        static::assertInstanceOf(Paginator::class, $trait->findPaginatedByOrFail([], [], 10));
    }

    public function testPaginator()
    {
        $trait = new PaginatorTraitStub();

        static::assertInstanceOf(Paginator::class, $trait->findPaginatedBy([], [], 10));
    }
}
