<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\CouchDB\DocumentRepository;

/**
 * CouchDB document repository.
 */
class CouchDBRepository extends DocumentRepository implements RepositoryInterface
{
    use RepositoryTrait;

    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return $this->getDocumentManager();
    }

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
    public function countAll()
    {
        return $this->countBy([]);
    }
}
