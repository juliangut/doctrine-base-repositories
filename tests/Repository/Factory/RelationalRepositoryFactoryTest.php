<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Tests\Factory;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Jgut\Doctrine\Repository\Factory\RelationalRepositoryFactory;
use Jgut\Doctrine\Repository\RelationalRepository;

/**
 * Relational repository factory tests.
 *
 * @group relational
 */
class RelationalRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCount()
    {
        $classMetadata = new ClassMetadata('RepositoryEntity');

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::any())
            ->method('getClassMetadata')
            ->will(static::returnValue($classMetadata));
        /* @var EntityManager $manager */

        $factory = new RelationalRepositoryFactory;

        $repository = $factory->getRepository($manager, 'RepositoryEntity');

        static::assertInstanceOf(RelationalRepository::class, $repository);
        static::assertEquals($repository, $factory->getRepository($manager, 'RepositoryEntity'));
    }
}
