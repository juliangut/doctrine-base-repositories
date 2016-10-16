<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Tests\Stubs;

use Doctrine\ORM\EntityManager;
use Jgut\Doctrine\Repository\Repository;
use Jgut\Doctrine\Repository\RepositoryTrait;

/**
 * Repository stub.
 */
class RepositoryStub implements Repository
{
    use RepositoryTrait;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityDocumentStub[]
     */
    protected $entities;

    /**
     * RepositoryMock constructor.
     *
     * @param EntityManager $entityManager
     * @param array         $entities
     */
    public function __construct(EntityManager $entityManager, array $entities = [])
    {
        $this->entityManager = $entityManager;
        $this->entities = $entities;
    }

    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return $this->entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return EntityDocumentStub::class;
    }

    /**
     * {@inheritdoc}
     */
    public function countBy($criteria)
    {
        return count($this->entities);
    }

    public function findPagedBy($criteria, array $orderBy = null, $limit = 10, $offset = 0)
    {
        // TODO: Implement findPagedBy() method.
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        if (is_array($id)) {
            return $this->entities;
        }

        return isset($this->entities[$id]) ? $this->entities[$id] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->entities;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->entities;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        return count($this->entities) ? $this->entities[0] : null;
    }

    /**
     * Get class metadata.
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    protected function getClassMetadata()
    {
        return new \Doctrine\ORM\Mapping\ClassMetadataInfo('EntityDocumentStub');
    }
}
