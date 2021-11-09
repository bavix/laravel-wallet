<?php

namespace Bavix\Wallet\Test;

use Illuminate\Foundation\Application;

/**
 * Trait RaceCondition.
 *
 * @property Application $app
 */
trait RaceCondition
{
    /**
     * The method involves working with the race.
     *
     * @before
     */
    public function enableRaceCondition(): bool
    {
        if (!$this->app) {
            $this->refreshApplication();
        }

        return true;
    }
}
