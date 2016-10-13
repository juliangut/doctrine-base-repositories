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
use Doctrine\ORM\EntityManager;
use Jgut\Doctrine\Repository\Pager\DefaultPage;
use Jgut\Doctrine\Repository\Pager\Page;
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

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))->method('getEventManager')->will(self::returnValue($eventManager));
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        $repository->disableEventSubscriber(EventStub::class);
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

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(7))->method('getEventManager')->will(self::returnValue($eventManager));
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

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

        self::assertEquals(DefaultPage::class, $repository->getPageClassName());

        $repository->setPageClassName(Page::class);

        self::assertEquals(Page::class, $repository->getPageClassName());
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

        $repository->setPageClassName(EventStub::class);
    }

    public function testFindOneOrCreate()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

        self::assertInstanceOf(EntityDocumentStub::class, $repository->findOneByOrCreateNew([]));
    }

    public function testSave()
    {
        $entity = new EntityDocumentStub;

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())->method('persist')->with(self::equalTo($entity));
        $manager->expects(self::once())->method('flush');
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager);

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

        $repository = new RepositoryStub($manager, [new EntityDocumentStub, new EntityDocumentStub]);

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

        $repository = new RepositoryStub($manager, [new EntityDocumentStub, new EntityDocumentStub]);

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

        $repository = new RepositoryStub($manager, [new EntityDocumentStub, new EntityDocumentStub]);

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

        $repository = new RepositoryStub($manager, [new EntityDocumentStub]);

        $repository->remove([]);
    }

    public function testCount()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, [new EntityDocumentStub, new EntityDocumentStub]);

        self::assertEquals(2, $repository->countBy([]));
    }
}
