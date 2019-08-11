<?php

namespace Bavix\Wallet\Test;

use Illuminate\Foundation\Application;

/**
 * Trait RaceCondition
 * @package Bavix\Wallet\Test
 * @property Application $app
 */
trait RaceCondition
{

    /**
     * The method involves working with the race.
     *
     * @before
     * @return bool
     */
    public function enableRaceCondition(): bool
    {
        if (!$this->app) {
            $this->refreshApplication();
        }

        $this->app['config']->set('wallet.lock.enabled', extension_loaded('memcached'));
        return true;
    }

}
