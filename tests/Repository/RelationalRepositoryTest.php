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

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Jgut\Doctrine\Repository\RelationalRepository;
use Jgut\Doctrine\Repository\Tests\Stubs\EntityDocumentStub;

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

        $repository = new RelationalRepository($manager, new ClassMetadata(EntityDocumentStub::class));

        static::assertEquals(EntityDocumentStub::class, $repository->getClassName());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Criteria must be an array of query fields or a Doctrine\ORM\QueryBuilder
     */
    public function testInvalidCriteria()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata(EntityDocumentStub::class));

        $repository->findPaginatedBy('');
    }

    public function testCount()
    {
        $query = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects(static::exactly(2))
            ->method('getSingleScalarResult')
            ->will(static::returnValue(10));

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([$manager])
            ->setMethodsExcept(['select', 'from', 'andWhere', 'setParameter', 'add'])
            ->getMock();
        $queryBuilder->expects(static::exactly(2))
            ->method('getQuery')
            ->will(static::returnValue($query));
        /* @var QueryBuilder $queryBuilder */

        $manager->expects(static::once())
            ->method('createQueryBuilder')
            ->will(static::returnValue($queryBuilder));
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata(EntityDocumentStub::class));

        static::assertEquals(10, $repository->countBy($queryBuilder));

        $queryBuilder->expects(static::exactly(2))
            ->method('getRootAliases')
            ->will(static::returnValue(['a']));

        static::assertEquals(10, $repository->countBy(['fakeField' => 'fakeValue', 'nullFakeField' => null]));
    }
}
