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

use Doctrine\Common\Persistence\ObjectRepository;

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
    protected $repositoryClassName = ObjectRepository::class;

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
