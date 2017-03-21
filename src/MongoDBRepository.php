<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Doctrine\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Jgut\Doctrine\Repository\Pagination\MongoDBAdapter;
use Jgut\Doctrine\Repository\Traits\EventsTrait;
use Jgut\Doctrine\Repository\Traits\PaginatorTrait;
use Jgut\Doctrine\Repository\Traits\RepositoryTrait;

/**
 * MongoDB document repository.
 */
class MongoDBRepository extends DocumentRepository implements Repository
{
    use RepositoryTrait;
    use EventsTrait;
    use PaginatorTrait;

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return ClassUtils::getRealClass(parent::getClassName());
    }

    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return $this->getDocumentManager();
    }

    /**
     * {@inheritdoc}
     *
     * @param array $criteria
     * @param array $orderBy
     * @param int   $itemsPerPage
     *
     * @return \Zend\Paginator\Paginator
     */
    public function findPaginatedBy($criteria, array $orderBy = [], $itemsPerPage = 10)
    {
        return $this->getPaginator(
            new MongoDBAdapter($this->getDocumentPersister()->loadAll($criteria, $orderBy)),
            $itemsPerPage
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param array $criteria
     *
     * @return int
     */
    public function countBy($criteria)
    {
        return $this->getDocumentPersister()->loadAll($criteria)->count();
    }
}
