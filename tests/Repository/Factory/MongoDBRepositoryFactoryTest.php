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

namespace Jgut\Doctrine\Repository\Tests\Factory;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Jgut\Doctrine\Repository\Factory\MongoDBRepositoryFactory;
use Jgut\Doctrine\Repository\MongoDBRepository;
use Jgut\Doctrine\Repository\Tests\Stubs\EntityDocumentStub;

/**
 * MongoDB repository factory tests.
 *
 * @group relational
 */
class MongoDBRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCount()
    {
        $classMetadata = new ClassMetadata(EntityDocumentStub::class);

        $uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::any())
            ->method('getUnitOfWork')
            ->will(static::returnValue($uow));
        $manager->expects(static::any())
            ->method('getClassMetadata')
            ->will(static::returnValue($classMetadata));
        /* @var DocumentManager $manager */

        $factory = new MongoDBRepositoryFactory();

        $repository = $factory->getRepository($manager, EntityDocumentStub::class);

        static::assertInstanceOf(MongoDBRepository::class, $repository);
        static::assertEquals($repository, $factory->getRepository($manager, EntityDocumentStub::class));
    }
}
