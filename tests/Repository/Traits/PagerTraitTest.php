<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Tests;

use Doctrine\ORM\EntityManager;
use Jgut\Doctrine\Repository\Pager\DefaultPager;
use Jgut\Doctrine\Repository\Pager\Pager;
use Jgut\Doctrine\Repository\Tests\Stubs\EntityDocumentStub;
use Jgut\Doctrine\Repository\Tests\Stubs\EventStub;
use Jgut\Doctrine\Repository\Tests\Stubs\RepositoryStub;

/**
 * Pager trait tests.
 *
 * @group repository
 */
class PagerTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testPageClassName()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        static::assertEquals(DefaultPager::class, $repository->getPagerClassName());

        $repository->setPagerClassName(Pager::class);

        static::assertEquals(Pager::class, $repository->getPagerClassName());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Invalid page class/
     */
    public function testBadPageClassName()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->setPagerClassName(EventStub::class);
    }

    public function testFindOneOrCreate()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        static::assertInstanceOf(EntityDocumentStub::class, $repository->findOneByOrGetNew([]));
    }
}
