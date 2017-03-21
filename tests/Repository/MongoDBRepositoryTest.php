<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Doctrine\Repository\Tests;

use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Persisters\DocumentPersister;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Jgut\Doctrine\Repository\MongoDBRepository;
use Jgut\Doctrine\Repository\Tests\Stubs\EntityDocumentStub;
use Zend\Paginator\Paginator;

/**
 * MongoDB repository tests.
 *
 * @group mongodb
 */
class MongoDBRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testDocumentName()
    {
        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var DocumentManager $manager */

        $uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var UnitOfWork $uow */

        $repository = new MongoDBRepository($manager, $uow, new ClassMetadata(EntityDocumentStub::class));

        static::assertEquals(EntityDocumentStub::class, $repository->getClassName());
    }

    public function testFindPaginated()
    {
        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var DocumentManager $manager */

        $cursor = $this->getMockBuilder(Cursor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $documentPersister = $this->getMockBuilder(DocumentPersister::class)
            ->disableOriginalConstructor()
            ->getMock();
        $documentPersister->expects(static::once())
            ->method('loadAll')
            ->will(static::returnValue($cursor));

        $uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uow->expects(static::once())
            ->method('getDocumentPersister')
            ->will(static::returnValue($documentPersister));
        /* @var UnitOfWork $uow */

        $repository = new MongoDBRepository($manager, $uow, new ClassMetadata(EntityDocumentStub::class));

        static::assertInstanceOf(Paginator::class, $repository->findPaginatedBy([], ['fakeField' => 'ASC']));
    }

    public function testCount()
    {
        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var DocumentManager $manager */

        $cursor = $this->getMockBuilder(Cursor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cursor->expects(static::once())
            ->method('count')
            ->will(static::returnValue(10));

        $documentPersister = $this->getMockBuilder(DocumentPersister::class)
            ->disableOriginalConstructor()
            ->getMock();
        $documentPersister->expects(static::once())
            ->method('loadAll')
            ->will(static::returnValue($cursor));

        $uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uow->expects(static::once())
            ->method('getDocumentPersister')
            ->will(static::returnValue($documentPersister));
        /* @var UnitOfWork $uow */

        $repository = new MongoDBRepository($manager, $uow, new ClassMetadata(EntityDocumentStub::class));

        static::assertEquals(10, $repository->countBy(['fakeField' => 'fakeValue', 'arrayFakeField' => []]));
    }
}
