<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Tests\Traits;

use Jgut\Doctrine\Repository\Tests\Stubs\PaginatorTraitStub;
use Zend\Paginator\Paginator;

/**
 * Paginator trait tests.
 */
class PaginatorTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testPaginator()
    {
        $trait = new PaginatorTraitStub();

        static::assertInstanceOf(Paginator::class, $trait->getPaginated([], 10));
    }
}
