<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Factory;

/**
 * Abstract base repository factory.
 */
abstract class AbstractRepositoryFactory
{
    /**
     * Default repository class.
     *
     * @var string
     */
    protected $repositoryClassName;

    /**
     * AbstractRepositoryFactory constructor.
     *
     * @param string $repositoryClassName
     */
    public function __construct($repositoryClassName)
    {
        $this->repositoryClassName = $repositoryClassName;
    }

    /**
     * Get default repository class.
     *
     * @return string
     */
    public function getDefaultRepositoryClassName()
    {
        return $this->repositoryClassName;
    }
}