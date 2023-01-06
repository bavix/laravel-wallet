<?php

declare(strict_types=1);

namespace Bavix\Wallet;

final class WalletConfigure
{
    private static bool $runsMigrations = true;

    /**
     * Needed for class testing.
     */
    public static function reset(): void
    {
        self::$runsMigrations = true;
    }

    /**
     * Configure Wallet to not register its migrations.
     */
    public static function ignoreMigrations(): void
    {
        self::$runsMigrations = false;
    }

    /**
     * Indicates if Wallet migrations will be run.
     */
    public static function isRunsMigrations(): bool
    {
        return self::$runsMigrations;
    }
}
