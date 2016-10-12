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

use Jgut\Doctrine\Repository\Pager\DefaultPage;

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
        new DefaultPage([], 0);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessageRegExp /^Page can not be higher than 1. 2 given/
     */
    public function testBadPageUpperLimit()
    {
        new DefaultPage([], 2);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessageRegExp /^Page size must be at least 1. 0 given/
     */
    public function testBadPageSize()
    {
        new DefaultPage([], 0, 0);
    }

    public function testPage()
    {
        $page = new DefaultPage(['a', 'b', 'c', 'd'], 1, 4, 12);

        self::assertEquals(4, $page->getPageSize());
        self::assertEquals(12, $page->getTotalCount());

        self::assertEquals(1, $page->getCurrentPage());
        self::assertTrue($page->isFirstPage());
        self::assertEquals(0, $page->getCurrentPageOffsetStart());
        self::assertEquals(4, $page->getCurrentPageOffsetEnd());

        self::assertNull($page->getPreviousPage());
        self::assertEquals(2, $page->getNextPage());

        self::assertEquals(3, $page->getTotalPages());
        self::assertFalse($page->isLastPage());
    }
}
