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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Jgut\Doctrine\Repository\MongoDBRepository;
use Jgut\Doctrine\Repository\Tests\Mocks\EntityDocumentMock;

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

        $repository = new MongoDBRepository($manager, $uow, new ClassMetadata(EntityDocumentMock::class));

        $repository->restoreEventSubscribers();

        self::assertEquals(EntityDocumentMock::class, $repository->getClassName());
    }
}
