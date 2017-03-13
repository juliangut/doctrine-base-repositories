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
use Jgut\Doctrine\Repository\RelationalRepository;
use Jgut\Doctrine\Repository\Tests\Stubs\BlankEventStub;
use Jgut\Doctrine\Repository\Tests\Stubs\EntityDocumentStub;
use Jgut\Doctrine\Repository\Tests\Stubs\EventStub;
use Jgut\Doctrine\Repository\Tests\Stubs\RepositoryStub;

/**
 * Events trait tests.
 *
 * @group repository
 */
class EventsTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testEventSubscribersManagement()
    {
        $eventSubscriber = new EventStub;

        $eventManager = new EventManager;
        $eventManager->addEventSubscriber($eventSubscriber);

        $manager = $this->getMockBuilder(MongoDBDocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::any())
            ->method('getEventManager')
            ->will(static::returnValue($eventManager));
        /* @var MongoDBDocumentManager $manager */

        $uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var UnitOfWork $uow */

        $repository = new MongoDBRepository($manager, $uow, new MongoDBClassMetadata(EntityDocumentStub::class));

        $repository->disableEventSubscriber($eventSubscriber);
        static::assertCount(0, $eventManager->getListeners('prePersist'));

        $repository->restoreEventSubscribers();
        static::assertCount(1, $eventManager->getListeners('prePersist'));

        $repository->disableEventSubscriber(BlankEventStub::class);
        static::assertCount(1, $eventManager->getListeners('prePersist'));
    }

    public function testSubscribedEvents()
    {
        $eventSubscriber = new EventStub();

        $eventManager = new EventManager;
        $eventManager->addEventSubscriber($eventSubscriber);

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::any())
            ->method('getEventManager')
            ->will(static::returnValue($eventManager));
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        self::assertEquals($eventSubscriber->getSubscribedEvents(), $repository->getRegisteredEvents());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage subscriberClass must be an EventSubscriber
     */
    public function testBadEventSubscriber()
    {
        /* @var EntityManager $manager */
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = new RepositoryStub($manager);

        $repository->disableEventSubscriber(new \stdClass);
    }

    public function testEventListenersManagement()
    {
        $eventManager = new EventManager;
        $eventManager->addEventSubscriber(new EventStub());

        $manager = $this->getMockBuilder(CouchDBDocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::any())
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
        $eventManager = new EventManager;
        $eventManager->addEventSubscriber(new EventStub());

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::any())
            ->method('getEventManager')
            ->will(static::returnValue($eventManager));
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata('RepositoryEntity'));

        $repository->disableEventListener('onFlush', EventStub::class);
        static::assertCount(0, $eventManager->getListeners('onFlush'));
        $repository->restoreEventListeners('onFlush');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage subscriberClass must be an EventSubscriber
     */
    public function testBadEventListener()
    {
        $eventManager = new EventManager;
        $eventManager->addEventSubscriber(new EventStub());

        /* @var EntityManager $manager */
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = new RepositoryStub($manager);

        $repository->disableEventListener('onFlush', new \stdClass);
    }
}
