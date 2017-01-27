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

use Doctrine\ORM\EntityManager;
use Jgut\Doctrine\Repository\Tests\Stubs\EntityDocumentStub;
use Jgut\Doctrine\Repository\Tests\Stubs\RepositoryStub;

/**
 * Repository trait tests.
 *
 * @group repository
 */
class RepositoryTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testAutoFlush()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        self::assertFalse($repository->isAutoFlush());

        $repository->setAutoFlush(true);

        self::assertTrue($repository->isAutoFlush());
    }

    public function testGetNewByFindOne()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $entity = $repository->findOneByOrGetNew([]);

        static::assertInstanceOf(EntityDocumentStub::class, $entity);
    }

    public function testFindOneOrGetNew()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $entity = new EntityDocumentStub;

        $repository = new RepositoryStub($manager, [$entity]);

        static::assertEquals($entity, $repository->findOneByOrGetNew([]));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Managed object must be a /
     */
    public function testInvalidAdd()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->add(new \stdClass);
    }

    public function testAdd()
    {
        $entity = new EntityDocumentStub;

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())->method('persist')->with(self::equalTo($entity));
        $manager->expects(static::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);
        $repository->setAutoFlush(true);

        $repository->add($entity);
    }

    public function testRemoveAll()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))->method('remove');
        $manager->expects(static::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, [new EntityDocumentStub, new EntityDocumentStub]);

        $repository->removeAll(true);
    }

    public function testRemoveBy()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))->method('remove');
        $manager->expects(static::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, [new EntityDocumentStub, new EntityDocumentStub]);

        $repository->removeBy([], true);
    }

    public function testRemoveOneBy()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())->method('remove');
        $manager->expects(static::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, [new EntityDocumentStub, new EntityDocumentStub]);

        $repository->removeOneBy([], true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Managed object must be a /
     */
    public function testInvalidRemove()
    {
        $entity = new \stdClass;

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->remove($entity, true);
    }

    public function testRemove()
    {
        $entity = new EntityDocumentStub;

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())->method('remove')->with(self::equalTo($entity));
        $manager->expects(static::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, [$entity]);

        $repository->remove(0, true);
    }

    public function testRemoveObject()
    {
        $entity = new EntityDocumentStub;

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())->method('remove')->with(self::equalTo($entity));
        $manager->expects(static::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->remove($entity, true);
    }

    public function testCount()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, [new EntityDocumentStub, new EntityDocumentStub]);

        static::assertEquals(2, $repository->countAll());
    }
}
