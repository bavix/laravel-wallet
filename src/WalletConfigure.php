<?php

namespace Bavix\Wallet;

class WalletConfigure
{
    /**
     * Indicates if Wallet migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * Configure Wallet to not register its migrations.
     *
     * @return static
     */
    public static function ignoreMigrations(): self
    {
        static::$runsMigrations = false;

        return new static;
    }
}
