<?php

/*
 * doctrine-repositories (https://github.com/juliangut/doctrine-repositories).
 * Doctrine2 utility repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Doctrine\Repository\Traits;

use Jgut\Doctrine\Repository\Pager\DefaultPager;
use Jgut\Doctrine\Repository\Pager\Pager;

/**
 * Pager trait.
 */
trait PagerTrait
{
    /**
     * Pager class name.
     *
     * @var string
     */
    protected $pagerClassName = DefaultPager::class;

    /**
     * {@inheritdoc}
     */
    public function getPagerClassName()
    {
        return $this->pagerClassName;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function setPagerClassName($className)
    {
        $reflectionClass = new \ReflectionClass($className);

        if (!$reflectionClass->implementsInterface(Pager::class)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid page class "%s". It must be a %s.',
                $className,
                Pager::class
            ));
        }

        $this->pagerClassName = $className;
    }
}
