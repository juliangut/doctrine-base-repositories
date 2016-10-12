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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Jgut\Doctrine\Repository\MongoDBRepository;
use Jgut\Doctrine\Repository\Tests\Stubs\EntityDocumentStub;

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

        $repository->restoreEventSubscribers();

        self::assertEquals(EntityDocumentStub::class, $repository->getClassName());
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /^You need to pass a parameter to "removeByParameter"$/
     */
    public function testCallNoArguments()
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

        $repository->removeByParameter();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /^Invalid remove by call/
     */
    public function testCallNoField()
    {
        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var DocumentManager $manager */

        $uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var UnitOfWork $uow */

        $metadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects(self::once())->method('hasField')->will(self::returnValue(false));
        $metadata->expects(self::once())->method('hasAssociation')->will(self::returnValue(false));
        /* @var ClassMetadata $metadata */

        $repository = new MongoDBRepository($manager, $uow, $metadata);

        $repository->removeOneByParameter(0);
    }
}
