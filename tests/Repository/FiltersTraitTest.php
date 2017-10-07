<?php

/*
 * doctrine-base-repositories (https://github.com/juliangut/doctrine-base-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-base-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Doctrine\Repository\Tests;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\FilterCollection;
use Jgut\Doctrine\Repository\Tests\Stubs\FilterStub;
use Jgut\Doctrine\Repository\Tests\Stubs\RepositoryStub;

/**
 * Filters trait tests.
 */
class FiltersTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testFiltersManagement()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $config = new Configuration();
        $config->addFilter('anything', new FilterStub($manager));

        $manager->expects(self::any())
            ->method('getConfiguration')
            ->will(static::returnValue($config));

        $filterCollection = new FilterCollection($manager);
        $filterCollection->enable('anything');

        $manager->expects(self::any())
            ->method('getFilters')
            ->will(static::returnValue($filterCollection));

        $repository = new RepositoryStub($manager);

        $repository->disableFilter('anything');
        static::assertCount(0, $filterCollection->getEnabledFilters());

        $repository->disableFilter('anything');
        static::assertCount(0, $filterCollection->getEnabledFilters());

        $repository->restoreFilter('unknown');
        static::assertCount(0, $filterCollection->getEnabledFilters());

        $repository->restoreFilter('anything');
        static::assertCount(1, $filterCollection->getEnabledFilters());

        $repository->disableFilters();
        static::assertCount(0, $filterCollection->getEnabledFilters());

        $repository->restoreFilters();
        static::assertCount(1, $filterCollection->getEnabledFilters());
    }
}
