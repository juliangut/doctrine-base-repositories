<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Tests\Pager;

use Jgut\Doctrine\Repository\Pager\DefaultPager;

/**
 * Default pager tests.
 *
 * @group pager
 */
class DefaultPageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessageRegExp /^Page can not be lower than 1. 0 given/
     */
    public function testBadPageLowerLimit()
    {
        new DefaultPager([], 0);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessageRegExp /^Page can not be higher than 1. 2 given/
     */
    public function testBadPageUpperLimit()
    {
        new DefaultPager([], 2);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessageRegExp /^Page size must be at least 1. 0 given/
     */
    public function testBadPageSize()
    {
        new DefaultPager([], 0, 0);
    }

    public function testPage()
    {
        $page = new DefaultPager(['a', 'b', 'c', 'd'], 1, 4, 12);

        static::assertEquals(4, $page->getPageSize());
        static::assertEquals(12, $page->getTotalCount());

        static::assertEquals(1, $page->getCurrentPage());
        static::assertTrue($page->isFirstPage());
        static::assertEquals(0, $page->getCurrentPageOffsetStart());
        static::assertEquals(4, $page->getCurrentPageOffsetEnd());

        static::assertNull($page->getPreviousPage());
        static::assertEquals(2, $page->getNextPage());

        static::assertEquals(3, $page->getTotalPages());
        static::assertFalse($page->isLastPage());

        static::assertEquals('b', $page[1]);
    }
}
