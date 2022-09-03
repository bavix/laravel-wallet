<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

final class ConfigService implements ConfigServiceInterface
{
    public function getClass(string $name, string $default): string
    {
        $value = $this->getString($name, $default);
        assert(class_exists($value));

        return $value;
    }

    public function getString(string $name, string $default): string
    {
        $value = config($name, $default);
        assert(is_string($value));

        return $value;
    }

    /**
     * @return array<mixed>
     */
    public function getArray(string $name): array
    {
        $value = config($name, []);
        assert(is_array($value));

        return $value;
    }
}
