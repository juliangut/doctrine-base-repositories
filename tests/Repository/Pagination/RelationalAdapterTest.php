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

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Jgut\Doctrine\Repository\Pagination\RelationalAdapter;

/**
 * RDBMS paginator adapter tests.
 *
 * @group relational
 */
class RelationalAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testItems()
    {
        /* @var Configuration $configuration */
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::any())
            ->method('getConfiguration')
            ->will(self::returnValue($configuration));
        /* @var EntityManager $manager */

        $query = new Query($manager);

        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paginator->expects(self::once())
            ->method('getQuery')
            ->will(self::returnValue($query));
        $paginator->expects(self::once())
            ->method('getIterator')
            ->will(self::returnValue([1, 2, 3]));
        /* @var Paginator $paginator */

        $adapter = new RelationalAdapter($paginator);

        static::assertEquals([1, 2, 3], $adapter->getItems(0, 10));
    }

    public function testCount()
    {
        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paginator->expects(self::once())
            ->method('count')
            ->will(self::returnValue(10));
        /* @var Paginator $paginator */

        $adapter = new RelationalAdapter($paginator);

        static::assertEquals(10, $adapter->count());
    }
}
