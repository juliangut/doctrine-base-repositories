<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Tests;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Jgut\Doctrine\Repository\Tests\Mocks\EntityDocumentMock;
use Jgut\Doctrine\Repository\Tests\Mocks\EventMock;
use Jgut\Doctrine\Repository\Tests\Mocks\RepositoryMock;

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
        $eventManager->addEventSubscriber(new EventMock);

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))->method('getEventManager')->will(self::returnValue($eventManager));
        /* @var EntityManager $manager */

        $repository = new RepositoryMock($manager);

        $repository->disableEventSubscriber(EventMock::class);
        self::assertCount(0, $eventManager->getListeners('prePersist'));

        $repository->restoreEventSubscribers();
        self::assertCount(1, $eventManager->getListeners('prePersist'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage subscriberClass must be class implementing EventSubscriber
     */
    public function testBadEventSubscriber()
    {
        $eventManager = new EventManager;
        $eventManager->addEventSubscriber(new EventMock);

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())->method('getEventManager')->will(self::returnValue($eventManager));
        /* @var EntityManager $manager */

        $repository = new RepositoryMock($manager);

        $repository->disableEventSubscriber(new \stdClass);
    }

    public function testEventListenersManagement()
    {
        $eventSubscriber = new EventMock;

        $eventManager = new EventManager;
        $eventManager->addEventSubscriber($eventSubscriber);

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(7))->method('getEventManager')->will(self::returnValue($eventManager));
        /* @var EntityManager $manager */

        $repository = new RepositoryMock($manager);

        $repository->disableEventListeners('onFlush');
        self::assertCount(0, $eventManager->getListeners('onFlush'));
        self::assertCount(1, $eventManager->getListeners('prePersist'));
        $repository->disableEventListeners('onFlush');

        $repository->restoreAllEventListeners();
        self::assertCount(1, $eventManager->getListeners('onFlush'));

        $repository->disableEventListeners('onFlush');
        self::assertCount(0, $eventManager->getListeners('onFlush'));

        $repository->restoreEventListeners('onFlush');
        self::assertCount(1, $eventManager->getListeners('onFlush'));
        $repository->restoreEventListeners('onFlush');

        $repository->disableEventListener('onFlush', $eventSubscriber);
        self::assertCount(0, $eventManager->getListeners('onFlush'));
        $repository->restoreEventListeners('onFlush');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage subscriberClass must be class implementing EventSubscriber
     */
    public function testBadEventListener()
    {
        $eventManager = new EventManager;
        $eventManager->addEventSubscriber(new EventMock);

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())->method('getEventManager')->will(self::returnValue($eventManager));
        /* @var EntityManager $manager */

        $repository = new RepositoryMock($manager);

        $repository->disableEventListener('onFlush', new \stdClass);
    }

    public function testFindOneOrCreate()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryMock($manager);

        self::assertInstanceOf(EntityDocumentMock::class, $repository->findOneByOrCreateNew([]));
    }

    public function testSave()
    {
        $entity = new EntityDocumentMock;

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())->method('persist')->with(self::equalTo($entity));
        $manager->expects(self::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryMock($manager);

        $repository->save($entity);
    }

    public function testRemoveAll()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))->method('remove');
        $manager->expects(self::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryMock($manager, [new EntityDocumentMock, new EntityDocumentMock]);

        $repository->removeAll();
    }

    public function testRemoveBy()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))->method('remove');
        $manager->expects(self::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryMock($manager, [new EntityDocumentMock, new EntityDocumentMock]);

        $repository->removeBy([]);
    }

    public function testRemoveOneBy()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())->method('remove');
        $manager->expects(self::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryMock($manager, [new EntityDocumentMock, new EntityDocumentMock]);

        $repository->removeOneBy([]);
    }

    public function testRemove()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())->method('remove');
        $manager->expects(self::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryMock($manager, [new EntityDocumentMock]);

        $repository->remove([]);
    }

    public function testCount()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryMock($manager, [new EntityDocumentMock, new EntityDocumentMock]);

        self::assertEquals(2, $repository->count([]));
    }
}
