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

namespace Jgut\Doctrine\Repository\Tests\Stubs;

use Doctrine\Common\EventSubscriber;

/**
 * Blank event stub.
 */
class BlankEventStub implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [];
    }
}
