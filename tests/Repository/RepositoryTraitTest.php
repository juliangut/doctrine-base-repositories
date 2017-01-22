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

use Doctrine\Common\EventManager;
use Doctrine\ODM\CouchDB\DocumentManager as CouchDBDocumentManager;
use Doctrine\ODM\CouchDB\Mapping\ClassMetadata as CouchDBClassMetadata;
use Doctrine\ODM\MongoDB\DocumentManager as MongoDBDocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as MongoDBClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Jgut\Doctrine\Repository\CouchDBRepository;
use Jgut\Doctrine\Repository\MongoDBRepository;
use Jgut\Doctrine\Repository\Pager\DefaultPager;
use Jgut\Doctrine\Repository\Pager\Pager;
use Jgut\Doctrine\Repository\RelationalRepository;
use Jgut\Doctrine\Repository\Tests\Stubs\EntityDocumentStub;
use Jgut\Doctrine\Repository\Tests\Stubs\EventStub;
use Jgut\Doctrine\Repository\Tests\Stubs\RepositoryStub;

/**
 * Repository trait tests.
 *
 * @group repository
 */
class RepositoryTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testEventSubscribersManagement()
    {
        $eventManager = new EventManager;
        $eventManager->addEventSubscriber(new EventStub);

        $manager = $this->getMockBuilder(MongoDBDocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))
            ->method('getEventManager')
            ->will(static::returnValue($eventManager));
        /* @var MongoDBDocumentManager $manager */

        $uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var UnitOfWork $uow */

        $repository = new MongoDBRepository($manager, $uow, new MongoDBClassMetadata(EntityDocumentStub::class));

        $repository->disableEventSubscriber(EventStub::class);
        static::assertCount(0, $eventManager->getListeners('prePersist'));

        $repository->restoreEventSubscribers();
        static::assertCount(1, $eventManager->getListeners('prePersist'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage subscriberClass must be a EventSubscriber
     */
    public function testBadEventSubscriber()
    {
        $eventManager = new EventManager;
        $eventManager->addEventSubscriber(new EventStub);

        /* @var EntityManager $manager */
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = new RepositoryStub($manager);

        $repository->disableEventSubscriber(new \stdClass);
    }

    public function testEventListenersManagement()
    {
        $eventSubscriber = new EventStub;

        $eventManager = new EventManager;
        $eventManager->addEventSubscriber($eventSubscriber);

        $manager = $this->getMockBuilder(CouchDBDocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(5))
            ->method('getEventManager')
            ->will(static::returnValue($eventManager));
        /* @var CouchDBDocumentManager $manager */

        $repository = new CouchDBRepository($manager, new CouchDBClassMetadata('RepositoryDocument'));

        $repository->disableEventListeners('onFlush');
        static::assertCount(0, $eventManager->getListeners('onFlush'));
        static::assertCount(1, $eventManager->getListeners('prePersist'));
        $repository->disableEventListeners('onFlush');

        $repository->restoreAllEventListeners();
        static::assertCount(1, $eventManager->getListeners('onFlush'));

        $repository->disableEventListeners('onFlush');
        static::assertCount(0, $eventManager->getListeners('onFlush'));

        $repository->restoreEventListeners('onFlush');
        static::assertCount(1, $eventManager->getListeners('onFlush'));
        $repository->restoreEventListeners('onFlush');
    }

    public function testEventListenerManagement()
    {
        $eventSubscriber = new EventStub;

        $eventManager = new EventManager;
        $eventManager->addEventSubscriber($eventSubscriber);

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))
            ->method('getEventManager')
            ->will(static::returnValue($eventManager));
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata('RepositoryEntity'));

        $repository->disableEventListener('onFlush', $eventSubscriber);
        static::assertCount(0, $eventManager->getListeners('onFlush'));
        $repository->restoreEventListeners('onFlush');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage subscriberClass must be a EventSubscriber
     */
    public function testBadEventListener()
    {
        $eventManager = new EventManager;
        $eventManager->addEventSubscriber(new EventStub);

        /* @var EntityManager $manager */
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = new RepositoryStub($manager);

        $repository->disableEventListener('onFlush', new \stdClass);
    }

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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Managed object must be a /
     */
    public function testInvalidSave()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->add(new \stdClass);
    }

    public function testSave()
    {
        $entity = new EntityDocumentStub;

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())->method('persist')->with(self::equalTo($entity));
        $manager->expects(static::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

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

        $repository->removeAll();
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

        $repository->removeBy([]);
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

        $repository->removeOneBy([]);
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

        $repository->remove($entity);
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

        $repository->remove(0);
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

        $repository->remove($entity);
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
