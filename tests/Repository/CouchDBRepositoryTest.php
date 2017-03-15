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

use Doctrine\ODM\CouchDB\DocumentManager;
use Doctrine\ODM\CouchDB\Mapping\ClassMetadata;
use Jgut\Doctrine\Repository\CouchDBRepository;
use Jgut\Doctrine\Repository\Tests\Stubs\EntityDocumentStub;
use Zend\Paginator\Paginator;

/**
 * CouchDB repository tests.
 *
 * @group couchdb
 */
class CouchDBRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testDocumentName()
    {
        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var DocumentManager $manager */

        $repository = new CouchDBRepository($manager, new ClassMetadata(EntityDocumentStub::class));

        static::assertEquals(EntityDocumentStub::class, $repository->getClassName());
    }

    public function testFindPaginated()
    {
        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var DocumentManager $manager */

        $repository = $this->getMockBuilder(CouchDBRepository::class)
            ->setConstructorArgs([$manager, new ClassMetadata(EntityDocumentStub::class)])
            ->setMethodsExcept(['findPaginatedBy'])
            ->getMock();
        $repository->expects(static::once())
            ->method('findBy')
            ->will(static::returnValue(['a', 'b']));
        /* @var CouchDBRepository $repository */

        static::assertInstanceOf(Paginator::class, $repository->findPaginatedBy(''));
    }

    public function testCount()
    {
        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var DocumentManager $manager */

        $repository = $this->getMockBuilder(CouchDBRepository::class)
            ->setConstructorArgs([$manager, new ClassMetadata(EntityDocumentStub::class)])
            ->setMethodsExcept(['countBy'])
            ->getMock();
        $repository->expects(static::once())
            ->method('findBy')
            ->will(static::returnValue(['a', 'b']));
        /* @var CouchDBRepository $repository */

        static::assertEquals(2, $repository->countBy([]));
    }
}
