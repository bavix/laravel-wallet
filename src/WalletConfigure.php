<?php

declare(strict_types=1);

namespace Bavix\Wallet;

final class WalletConfigure
{
    private static bool $runsMigrations = true;

    /**
     * Reset the static property runsMigrations to true.
     *
     * This method is used to reset the static property runsMigrations to its default value of true.
     * It is typically used after using the `ignoreMigrations` method to ignore the package migrations.
     */
    public static function reset(): void
    {
        self::$runsMigrations = true;
    }

    /**
     * Configure Wallet to not register its migrations.
     *
     * This method is used to prevent the package migrations from being registered.
     * It is typically used in cases where you want to manage your own migrations.
     */
    public static function ignoreMigrations(): void
    {
        // Set the static property runsMigrations to false
        // This prevents the package migrations from being registered
        self::$runsMigrations = false;
    }

    /**
     * Indicates if Wallet migrations will be run.
     *
     * @return bool
     *     True if the migrations will be run, false if they will be ignored.
     */
    public static function isRunsMigrations(): bool
    {
        // Returns the value of the $runsMigrations property.
        // This property is used to determine whether or not the package migrations
        // will be registered. If it is true, the migrations will be run. If it is
        // false, the migrations will be ignored.
        return self::$runsMigrations;
    }
}
