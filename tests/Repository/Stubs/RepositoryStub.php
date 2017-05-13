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

namespace Jgut\Doctrine\Repository\Tests\Stubs;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Jgut\Doctrine\Repository\EventsTrait;
use Jgut\Doctrine\Repository\Repository;
use Jgut\Doctrine\Repository\RepositoryTrait;
use Zend\Paginator\Paginator;

/**
 * Repository stub.
 */
class RepositoryStub implements Repository
{
    use RepositoryTrait {
        doFlush as traitFlushObject;
    }
    use EventsTrait;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityStub[]
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
    protected function getManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(): string
    {
        return EntityStub::class;
    }

    /**
     * {@inheritdoc}
     */
    public function countBy($criteria): int
    {
        return count($this->entities);
    }

    /**
     * {@inheritdoc}
     */
    public function findPaginatedBy($criteria, array $orderBy = null, int $itemsPerPage = 10): Paginator
    {
        // Implementation not needed
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
    public function findAll(): array
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
    protected function getClassMetadata(): ClassMetadata
    {
        return new \Doctrine\ORM\Mapping\ClassMetadataInfo(self::class);
    }
}
