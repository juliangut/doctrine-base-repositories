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

namespace Jgut\Doctrine\Repository\Factory;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\RepositoryFactory;
use Jgut\Doctrine\Repository\MongoDBRepository;

/**
 * MongoDB document repository factory.
 */
class MongoDBRepositoryFactory extends AbstractRepositoryFactory implements RepositoryFactory
{
    /**
     * The list of DocumentRepository instances.
     *
     * @var \Doctrine\Common\Persistence\ObjectRepository[]
     */
    private $repositoryList = [];

    /**
     * MongoDBRepositoryFactory constructor.
     */
    public function __construct()
    {
        parent::__construct(MongoDBRepository::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(DocumentManager $documentManager, $documentName)
    {
        $repositoryHash =
            $documentManager->getClassMetadata($documentName)->getName() . spl_object_hash($documentManager);

        if (array_key_exists($repositoryHash, $this->repositoryList)) {
            return $this->repositoryList[$repositoryHash];
        }

        $this->repositoryList[$repositoryHash] = $this->createRepository($documentManager, $documentName);

        return $this->repositoryList[$repositoryHash];
    }

    /**
     * Create a new repository instance for a document class.
     *
     * @param DocumentManager $documentManager
     * @param string          $documentName
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function createRepository(DocumentManager $documentManager, $documentName)
    {
        $metadata = $documentManager->getClassMetadata($documentName);
        $repositoryClassName = $metadata->customRepositoryClassName ?: $this->getDefaultRepositoryClassName();

        return new $repositoryClassName($documentManager, $documentManager->getUnitOfWork(), $metadata);
    }
}
