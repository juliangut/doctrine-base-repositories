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

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Filter stub.
 */
class FilterStub extends SQLFilter
{
    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        // Check if the entity implements the LocalAware interface
        if (!$targetEntity->reflClass->implementsInterface('anything')) {
            return '';
        }

        return $targetTableAlias . '.anything = ' . $this->getParameter('anything');
    }
}
