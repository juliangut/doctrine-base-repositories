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
 * @group mongodb
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
}
