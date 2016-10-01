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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Jgut\Doctrine\Repository\RelationalRepository;

/**
 * Relational repository tests.
 *
 * @group relational
 */
class RelationalRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testEntityName()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata('RepositoryEntity'));

        $repository->restoreEventSubscribers();

        self::assertEquals('RepositoryEntity', $repository->getClassName());
    }
}
