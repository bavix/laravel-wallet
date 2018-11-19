<?php

namespace Bavix\Wallet;

class WalletProxy
{

    /**
     * @var array
     */
    protected static $rows = [];

    /**
     * @param int $key
     * @return bool
     */
    public static function has(int $key): bool
    {
        return \array_key_exists($key, static::$rows);
    }

    /**
     * @param int $key
     * @return int
     */
    public static function get(int $key): int
    {
        return (int) (static::$rows[$key] ?? 0);
    }

    /**
     * @param int $key
     * @param int $value
     */
    public static function set(int $key, int $value): void
    {
        static::$rows[$key] = $value;
    }

    /**
     * @return void
     */
    public static function fresh(): void
    {
        static::$rows = [];
    }

}
