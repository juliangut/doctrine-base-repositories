<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Tests\Paginator;

use Doctrine\MongoDB\EagerCursor;
use Doctrine\ODM\MongoDB\Cursor;
use Jgut\Doctrine\Repository\Pagination\MongoDBAdapter;

/**
 * MongoDB paginator adapter tests.
 *
 * @group mongo
 */
class MongoDBAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testItems()
    {
        $cursor = $this->getMockBuilder(Cursor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cursor->expects(self::any())
            ->method('recreate');
        $cursor->expects(self::any())
            ->method('skip');
        $cursor->expects(self::any())
            ->method('limit');
        $cursor->expects(self::any())
            ->method('toArray')
            ->will(self::returnValue([1, 2, 3]));
        /* @var Cursor $cursor */

        $adapter = new MongoDBAdapter($cursor);

        static::assertEquals([1, 2, 3], $adapter->getItems(0, 10));
    }

    public function testCount()
    {
        $cursor = $this->getMockBuilder(Cursor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cursor->expects(self::any())
            ->method('count')
            ->will(self::returnValue(10));
        /* @var Cursor $cursor */

        $adapter = new MongoDBAdapter($cursor);

        static::assertEquals(10, $adapter->count());
    }

    public function testEagerCount()
    {
        $baseCursor = $this->getMockBuilder(Cursor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $baseCursor->expects(self::any())
            ->method('count')
            ->will(self::returnValue(20));
        /* @var Cursor $baseCursor */

        $eagerCursor = $this->getMockBuilder(EagerCursor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eagerCursor->expects(self::any())
            ->method('getCursor')
            ->will(self::returnValue($baseCursor));
        /* @var EagerCursor $eagerCursor */

        $cursor = $this->getMockBuilder(Cursor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cursor->expects(self::any())
            ->method('getBaseCursor')
            ->will(self::returnValue($eagerCursor));
        /* @var Cursor $cursor */

        $adapter = new MongoDBAdapter($cursor);

        static::assertEquals(20, $adapter->count());
    }
}
