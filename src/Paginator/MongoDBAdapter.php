<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Paginator;

use Doctrine\MongoDB\EagerCursor;
use Doctrine\ODM\MongoDB\Cursor;
use Zend\Paginator\Adapter\AdapterInterface;

/**
 * MongoDB paginator adapter.
 */
class MongoDBAdapter implements AdapterInterface
{
    /**
     * @var Cursor
     */
    protected $cursor;

    /**
     * Adapter constructor.
     *
     * @param Cursor $cursor
     */
    public function __construct(Cursor $cursor)
    {
        $this->cursor = $cursor;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $cursor = clone $this->cursor;
        $cursor->recreate();
        $cursor->skip($offset);
        $cursor->limit($itemCountPerPage);

        return $cursor->toArray(false);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        // Avoid using EagerCursor::count as this stores a collection without limits to memory
        if ($this->cursor->getBaseCursor() instanceof EagerCursor) {
            return $this->cursor->getBaseCursor()->getCursor()->count();
        }

        return $this->cursor->count();
    }
}
