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

namespace Jgut\Doctrine\Repository;

/**
 * Filters trait.
 */
trait FiltersTrait
{
    /**
     * List of disabled filters.
     *
     * @var object[]
     */
    protected $disabledFilters = [];

    /**
     * Disable all filters.
     */
    public function disableFilters()
    {
        foreach (\array_keys($this->getFilterCollection()->getEnabledFilters()) as $filter) {
            $this->disableFilter($filter);
        }
    }

    /**
     * Disable filter.
     *
     * @param string $filter
     */
    public function disableFilter(string $filter)
    {
        if (\in_array($filter, $this->disabledFilters, true)) {
            return;
        }

        $this->getFilterCollection()->disable($filter);

        $this->disabledFilters[] = $filter;
    }

    /**
     * Restore all disabled filters.
     */
    public function restoreFilters()
    {
        $filterCollection = $this->getFilterCollection();

        foreach ($this->disabledFilters as $filter) {
            $filterCollection->enable($filter);
        }

        $this->disabledFilters = [];
    }

    /**
     * Restore disabled filter.
     *
     * @param string $filter
     */
    public function restoreFilter(string $filter)
    {
        $position = \array_search($filter, $this->disabledFilters, true);
        if ($position === false) {
            return;
        }

        $this->getFilterCollection()->enable($filter);

        \array_splice($this->disabledFilters, $position, 1);
    }

    /**
     * Get filter collection.
     *
     * @return object
     */
    abstract protected function getFilterCollection();

    /**
     * Get object manager.
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    abstract protected function getManager();
}
