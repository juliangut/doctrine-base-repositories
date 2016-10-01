<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Tests\Mocks;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Jgut\Doctrine\Repository\RepositoryInterface;
use Jgut\Doctrine\Repository\RepositoryTrait;

/**
 * Repository mock.
 */
class RepositoryMock implements RepositoryInterface, ObjectRepository
{
    use RepositoryTrait;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityDocumentMock[]
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
        return EntityDocumentMock::class;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        return 100;
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
}