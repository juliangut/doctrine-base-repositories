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

use Doctrine\ODM\CouchDB\DocumentManager;
use Doctrine\ODM\CouchDB\Mapping\ClassMetadata;
use Jgut\Doctrine\Repository\CouchDBRepository;

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

        $repository->restoreEventSubscribers();

        self::assertEquals('RepositoryDocument', $repository->getClassName());
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
        $metadata->expects(self::once())->method('hasField')->will(self::returnValue(false));
        /* @var ClassMetadata $metadata */

        $repository = new CouchDBRepository($manager, $metadata);

        $repository->removeOneByParameter(0);
    }
}
