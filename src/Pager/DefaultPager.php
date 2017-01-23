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

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Default pager.
 */
class DefaultPager extends ArrayCollection implements Pager
{
    /**
     * Current page.
     *
     * @var int
     */
    protected $currentPage;

    /**
     * Total number of pages.
     *
     * @var int
     */
    protected $totalPages;

    /**
     * Page size.
     *
     * @var int
     */
    protected $pageCount;

    /**
     * Total number of elements.
     *
     * @var int
     */
    protected $totalCount;

    /**
     * {@inheritdoc}
     *
     * @throws \OutOfBoundsException
     */
    public function __construct(array $elements, $currentPage = 1, $pageCount = 10, $totalCount = 0)
    {
        $this->setCurrentPage($currentPage);
        $this->setPageCount($pageCount);

        $elements = array_slice($elements, 0, $this->pageCount);

        if ((int) $totalCount < 1) {
            $totalCount = count($elements);
        }
        $this->totalCount = (int) $totalCount;

        $this->totalPages = max(1, (int) ceil($this->totalCount / $this->pageCount));

        if ($this->currentPage > $this->totalPages) {
            throw new \OutOfBoundsException(sprintf(
                'Current page can not be higher than %d. %d given',
                $this->totalPages,
                $this->currentPage
            ));
        }

        parent::__construct($elements);
    }

    /**
     * Set current page.
     *
     * @param int $currentPage
     *
     * @throws \OutOfBoundsException
     */
    protected function setCurrentPage($currentPage)
    {
        if ((int) $currentPage < 1) {
            throw new \OutOfBoundsException(sprintf('Current page can not be lower than 1. %s given', $currentPage));
        }

        $this->currentPage = (int) $currentPage;
    }

    /**
     * Set pager page size.
     *
     * @param int $pageCount
     *
     * @throws \OutOfBoundsException
     */
    protected function setPageCount($pageCount)
    {
        if ((int) $pageCount < 1) {
            throw new \OutOfBoundsException(sprintf('Page count must be at least 1. %d given', $pageCount));
        }

        $this->pageCount = (int) $pageCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPageOffsetStart()
    {
        return ($this->currentPage - 1) * $this->pageCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPageOffsetEnd()
    {
        return min($this->totalCount, $this->getCurrentPageOffsetStart() + $this->pageCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousPage()
    {
        return $this->isFirstPage() ? null : $this->currentPage - 1;
    }

    /**
     * {@inheritdoc}
     */
    public function isFirstPage()
    {
        return $this->currentPage === 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getNextPage()
    {
        return $this->isLastPage() ? null : $this->currentPage + 1;
    }

    /**
     * {@inheritdoc}
     */
    public function isLastPage()
    {
        return $this->currentPage === $this->totalPages;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageCount()
    {
        return $this->pageCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }
}
