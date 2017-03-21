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

namespace Jgut\Doctrine\Repository\Tests\Factory;

use Doctrine\ODM\CouchDB\Mapping\ClassMetadata;
use Jgut\Doctrine\ManagerBuilder\CouchDB\DocumentManager;
use Jgut\Doctrine\Repository\CouchDBRepository;
use Jgut\Doctrine\Repository\Factory\CouchDBRepositoryFactory;

/**
 * CouchDB repository factory tests.
 *
 * @group relational
 */
class CouchDBRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCount()
    {
        $classMetadata = new ClassMetadata('RepositoryDocument');

        $manager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::any())
            ->method('getClassMetadata')
            ->will(static::returnValue($classMetadata));
        /* @var DocumentManager $manager */

        $factory = new CouchDBRepositoryFactory();

        $repository = $factory->getRepository($manager, 'RepositoryEntity');

        static::assertInstanceOf(CouchDBRepository::class, $repository);
        static::assertEquals($repository, $factory->getRepository($manager, 'RepositoryEntity'));
    }
}
