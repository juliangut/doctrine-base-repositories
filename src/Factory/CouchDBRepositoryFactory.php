<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Factory;

use Jgut\Doctrine\ManagerBuilder\CouchDB\DocumentManager;
use Jgut\Doctrine\ManagerBuilder\CouchDB\Repository\RepositoryFactory;
use Jgut\Doctrine\Repository\CouchDBRepository;

/**
 * Default CouchDB document repository factory.
 */
class CouchDBRepositoryFactory extends AbstractRepositoryFactory implements RepositoryFactory
{
    /**
     * Default repository class.
     *
     * @var string
     */
    protected $repositoryClassName = CouchDBRepository::class;

    /**
     * The list of DocumentRepository instances.
     *
     * @var \Doctrine\Common\Persistence\ObjectRepository[]
     */
    private $repositoryList = [];

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
        /* @var $metadata \Doctrine\ODM\CouchDB\Mapping\ClassMetadata */
        $metadata = $documentManager->getClassMetadata($documentName);
        $repositoryClassName = $metadata->customRepositoryClassName ?: $this->getDefaultRepositoryClassName();

        return new $repositoryClassName($documentManager, $metadata);
    }
}
