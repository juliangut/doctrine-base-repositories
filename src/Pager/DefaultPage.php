<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Pager;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Default page.
 */
class DefaultPage extends ArrayCollection implements Page
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
    protected $pageSize;

    /**
     * Total number of elements.
     *
     * @var int
     */
    protected $totalSize;

    /**
     * {@inheritdoc}
     *
     * @throws \OutOfBoundsException
     */
    public function __construct(array $elements, $page = 1, $pageSize = 10, $totalSize = 0)
    {
        if ((int) $pageSize < 1) {
            throw new \OutOfBoundsException(sprintf('Page size must be at least 1. %d given', $pageSize));
        }
        $this->pageSize = (int) $pageSize;

        if ((int) $page < 1) {
            throw new \OutOfBoundsException(sprintf('Page can not be lower than 1. %s given', $page));
        }
        $this->currentPage = (int) $page;

        $elements = array_slice($elements, 0, $this->pageSize);

        if ((int) $totalSize < 1) {
            $totalSize = count($elements);
        }
        $this->totalSize = (int) $totalSize;

        $this->totalPages = max(1, ceil($this->totalSize / $this->pageSize));

        if ($this->currentPage > $this->totalPages) {
            throw new \OutOfBoundsException(sprintf(
                'Page can not be higher than %d. %d given',
                $this->totalPages,
                $this->currentPage
            ));
        }

        parent::__construct($elements);
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
        return ($this->currentPage - 1) * $this->pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPageOffsetEnd()
    {
        return min($this->totalSize, $this->getCurrentPageOffsetStart() + $this->pageSize);
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
    public function getPageSize()
    {
        return $this->pageSize;
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
        return $this->totalSize;
    }
}
