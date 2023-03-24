<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Helpers;

final class Config
{
    public static function string(string $key, string $default): string
    {
        $value = config($key, $default);
        assert(is_string($value));

        return $value;
    }

    /**
     * @param class-string $default
     *
     * @return class-string
     */
    public static function classString(string $key, string $default): string
    {
        $value = self::string($key, $default);
        assert(class_exists($value));

        return $value;
    }
}
