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
use Jgut\Doctrine\Repository\Pager\Pager;

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

        $repository = new CouchDBRepository($manager, new ClassMetadata('RepositoryDocument'));

        static::assertEquals('RepositoryDocument', $repository->getClassName());
    }

    public function testFindPaged()
    {
        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var DocumentManager $manager */

        $repository = $this->getMockBuilder(CouchDBRepository::class)
            ->setConstructorArgs([$manager, new ClassMetadata('RepositoryDocument')])
            ->setMethodsExcept(['findPagedBy', 'getPagerClassName'])
            ->getMock();
        $repository->expects(static::once())
            ->method('findBy')
            ->will(static::returnValue(['a', 'b']));
        $repository->expects(static::once())
            ->method('countBy')
            ->will(static::returnValue(10));
        /* @var CouchDBRepository $repository */

        static::assertInstanceOf(Pager::class, $repository->findPagedBy(''));
    }

    public function testCount()
    {
        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var DocumentManager $manager */

        $repository = $this->getMockBuilder(CouchDBRepository::class)
            ->setConstructorArgs([$manager, new ClassMetadata('RepositoryDocument')])
            ->setMethodsExcept(['countBy'])
            ->getMock();
        $repository->expects(static::once())
            ->method('findBy')
            ->will(static::returnValue(['a', 'b']));
        /* @var CouchDBRepository $repository */

        static::assertEquals(2, $repository->countBy([]));
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /^Undefined method: "noMethod"/
     */
    public function testCallNoMethod()
    {
        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var DocumentManager $manager */

        $repository = new CouchDBRepository($manager, new ClassMetadata('RepositoryDocument'));

        $repository->noMethod();
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

        $repository = new CouchDBRepository($manager, new ClassMetadata('RepositoryDocument'));

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

        $metadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects(static::once())
            ->method('hasField')
            ->will(static::returnValue(false));
        /* @var ClassMetadata $metadata */

        $repository = new CouchDBRepository($manager, $metadata);

        $repository->removeOneByParameter(0);
    }
}
