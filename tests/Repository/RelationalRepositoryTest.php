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
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Jgut\Doctrine\Repository\RelationalRepository;
use Jgut\Doctrine\Repository\Tests\Stubs\EntityDocumentStub;
use Zend\Paginator\Paginator;

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
        static::assertEquals(10, $repository->countBy(['fakeField' => 'fakeValue', 'nullFakeField' => null]));
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /^You need to pass a parameter to .+::removeByParameter$/
     */
    public function testCallNoArguments()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata(EntityDocumentStub::class));

        $repository->removeByParameter();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /^Invalid call to .+::removeOneBy/
     */
    public function testCallNoField()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $metadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->name = EntityDocumentStub::class;
        $metadata->expects(static::once())->method('hasField')->will(static::returnValue(false));
        $metadata->expects(static::once())->method('hasAssociation')->will(static::returnValue(false));
        /* @var ClassMetadata $metadata */

        $repository = new RelationalRepository($manager, $metadata);

        $repository->removeOneByParameter(0);
    }
}
