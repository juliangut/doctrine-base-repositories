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

namespace Jgut\Doctrine\Repository\Factory;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;
use Jgut\Doctrine\Repository\RelationalRepository;

/**
 * Relational entity repository factory.
 */
class RelationalRepositoryFactory extends AbstractRepositoryFactory implements RepositoryFactory
{
    /**
     * The list of EntityRepository instances.
     *
     * @var \Doctrine\Common\Persistence\ObjectRepository[]
     */
    private $repositoryList = [];

    /**
     * RelationalRepositoryFactory constructor.
     */
    public function __construct()
    {
        parent::__construct(RelationalRepository::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $repositoryHash = $entityManager->getClassMetadata($entityName)->getName() . spl_object_hash($entityManager);

        if (array_key_exists($repositoryHash, $this->repositoryList)) {
            return $this->repositoryList[$repositoryHash];
        }

        $this->repositoryList[$repositoryHash] = $this->createRepository($entityManager, $entityName);

        return $this->repositoryList[$repositoryHash];
    }

    /**
     * Create a new repository instance for an entity class.
     *
     * @param EntityManager $entityManager
     * @param string        $entityName
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function createRepository(EntityManager $entityManager, $entityName)
    {
        $metadata = $entityManager->getClassMetadata($entityName);
        $repositoryClassName = $metadata->customRepositoryClassName ?: $this->getDefaultRepositoryClassName();

        return new $repositoryClassName($entityManager, $metadata);
    }
}
