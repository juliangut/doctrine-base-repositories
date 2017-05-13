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

use Doctrine\ORM\EntityManager;
use Jgut\Doctrine\Repository\Tests\Stubs\EntityStub;
use Jgut\Doctrine\Repository\Tests\Stubs\RepositoryStub;

/**
 * Repository trait tests.
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

    public function testFlush()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())
            ->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->flush();
    }

    public function testGetNewByFindOne()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        static::assertInstanceOf(EntityStub::class, $repository->findOneByOrGetNew([]));
    }

    public function testFindOneOrGetNew()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $entity = new EntityStub();

        $repository = new RepositoryStub($manager, [$entity]);

        static::assertEquals($entity, $repository->findOneByOrGetNew([]));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Object factory must return an instance of .+\. "boolean" returned$/
     */
    public function testInvalidObjectFactory()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->setObjectFactory(function () {
            return false;
        });

        $repository->getNew();
    }

    public function testGetNewByObjectFactory()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);
        $repository->setObjectFactory(function () {
            return new EntityStub();
        });

        static::assertInstanceOf(EntityStub::class, $repository->getNew());
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

        $repository->add(new \stdClass());
    }

    public function testAdd()
    {
        $entity = new EntityStub();

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())
            ->method('persist')
            ->with(self::equalTo($entity));
        $manager->expects(static::once())
            ->method('flush');
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
        $manager->expects(self::exactly(2))
            ->method('remove');
        $manager->expects(static::once())
            ->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, [new EntityStub(), new EntityStub()]);

        $repository->removeAll(true);
    }

    public function testRemoveBy()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))
            ->method('remove');
        $manager->expects(static::once())
            ->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, [new EntityStub(), new EntityStub()]);

        $repository->removeBy([], true);
    }

    public function testRemoveOneBy()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())
            ->method('remove');
        $manager->expects(static::once())
            ->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, [new EntityStub(), new EntityStub()]);

        $repository->removeOneBy([], true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Managed object must be a /
     */
    public function testInvalidRemove()
    {
        $entity = new \stdClass();

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->remove($entity, true);
    }

    public function testRemove()
    {
        $entity = new EntityStub();

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())
            ->method('remove')
            ->with(self::equalTo($entity));
        $manager->expects(static::once())
            ->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, [$entity]);

        $repository->remove(0, true);
    }

    public function testRemoveObject()
    {
        $entity = new EntityStub();

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())
            ->method('remove')
            ->with(self::equalTo($entity));
        $manager->expects(static::once())
            ->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->remove($entity, true);
    }

    public function testRefreshObject()
    {
        $entity = new EntityStub();

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())
            ->method('refresh')
            ->with(self::equalTo($entity));
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->refresh($entity);
    }

    public function testDetachObject()
    {
        $entity = new EntityStub();

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())
            ->method('detach')
            ->with(self::equalTo($entity));
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->detach($entity);
    }

    public function testCountAll()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, [new EntityStub(), new EntityStub()]);

        static::assertEquals(2, $repository->countAll());
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /^You need to call .+::removeByParameter with a parameter$/
     */
    public function testCallNoArguments()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->removeByParameter();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /^Undefined method "noMethod"\. Method call must start with one of(,? ".+")+!/
     */
    public function testCallNoMethod()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->noMethod(0);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /^Invalid call to .+::removeOneBy\. Field "parameter" does not exist/
     */
    public function testCallNoField()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->removeOneByParameter(0);
    }
}
