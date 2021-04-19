<?php

declare(strict_types=1);

namespace Bavix\Wallet;

/**
 * Class WalletConfigure.
 * @codeCoverageIgnore
 */
class WalletConfigure
{
    /**
     * Indicates if Wallet migrations will be run.
     *
     * @var bool
     */
    public static bool $runsMigrations = true;

    /**
     * WalletConfigure constructor.
     */
    final public function __construct()
    {
    }

    /**
     * Configure Wallet to not register its migrations.
     *
     * @return static
     */
    public static function ignoreMigrations(): self
    {
        static::$runsMigrations = false;

        return new static();
    }
}
