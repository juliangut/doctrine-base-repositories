<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Pager;

/**
 * Pager interface.
 */
interface Pager extends \Countable, \IteratorAggregate
{
    /**
     * Page constructor.
     *
     * @param array $elements
     * @param int   $currentPage
     * @param int   $pageCount
     * @param int   $totalCount
     */
    public function __construct(array $elements, $currentPage = 1, $pageCount = 10, $totalCount = 0);

    /**
     * Get current page.
     *
     * @return int
     */
    public function getCurrentPage();

    /**
     * Get current page offset start.
     *
     * @return int
     */
    public function getCurrentPageOffsetStart();

    /**
     * Get current page offset end.
     *
     * @return mixed
     */
    public function getCurrentPageOffsetEnd();

    /**
     * Get previous page.
     *
     * @return int|null
     */
    public function getPreviousPage();

    /**
     * Is current page the first page.
     *
     * @return bool
     */
    public function isFirstPage();

    /**
     * Get next page.
     *
     * @return int|null
     */
    public function getNextPage();

    /**
     * Is current page the last page.
     *
     * @return bool
     */
    public function isLastPage();

    /**
     * Get page size.
     *
     * @return int
     */
    public function getPageCount();

    /**
     * Get total number of pages.
     *
     * @return int
     */
    public function getTotalPages();

    /**
     * Get total number of elements.
     *
     * @return int
     */
    public function getTotalCount();
}
