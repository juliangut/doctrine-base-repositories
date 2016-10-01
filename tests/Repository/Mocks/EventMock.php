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

use Doctrine\Common\EventSubscriber;

/**
 * Event mock.
 */
class EventMock implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'onFlush',
        ];
    }
}
