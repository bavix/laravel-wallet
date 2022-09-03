<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface ConfigServiceInterface
{
    /**
     * @param class-string $default
     * @return class-string
     */
    public function getClass(string $name, string $default): string;

    public function getString(string $name, string $default): string;

    /**
     * @return array<mixed>
     */
    public function getArray(string $name): array;
}
