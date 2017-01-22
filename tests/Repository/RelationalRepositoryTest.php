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
use Jgut\Doctrine\Repository\Pager\Pager;
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

        static::assertEquals('RepositoryEntity', $repository->getClassName());
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

        $repository = new RelationalRepository($manager, new ClassMetadata('RepositoryEntity'));

        $repository->findPagedBy('');
    }

    public function testFindPaged()
    {
        $query = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects(static::once())
            ->method('getResult')
            ->will(static::returnValue(['a', 'b']));

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([$manager])
            ->setMethodsExcept([
                'select',
                'from',
                'andWhere',
                'setParameter',
                'add',
                'getRootAliases',
                'addOrderBy',
                'setFirstResult',
                'setMaxResults',
            ])
            ->getMock();
        $queryBuilder->expects(static::exactly(2))
            ->method('getQuery')
            ->will(static::returnValue($query));
        /* @var QueryBuilder $queryBuilder */

        $repository = new RelationalRepository($manager, new ClassMetadata('RepositoryEntity'));

        static::assertInstanceOf(Pager::class, $repository->findPagedBy($queryBuilder, ['fakeField' => 'ASC']));
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

        $repository = new RelationalRepository($manager, new ClassMetadata('RepositoryEntity'));

        static::assertEquals(10, $repository->countBy($queryBuilder));
        static::assertEquals(10, $repository->countBy(['fakeField' => 'fakeValue', 'nullFakeField' => null]));
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /^You need to pass a parameter to "removeByParameter"$/
     */
    public function testCallNoArguments()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata('RepositoryEntity'));

        $repository->removeByParameter();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /^Invalid remove by call/
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
        $metadata->expects(static::once())->method('hasField')->will(static::returnValue(false));
        $metadata->expects(static::once())->method('hasAssociation')->will(static::returnValue(false));
        /* @var ClassMetadata $metadata */

        $repository = new RelationalRepository($manager, $metadata);

        $repository->removeOneByParameter(0);
    }
}
