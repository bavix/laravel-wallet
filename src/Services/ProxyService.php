<?php

namespace Bavix\Wallet\Services;

use ArrayAccess;
use Bavix\Wallet\Interfaces\Storable;
use function array_key_exists;

/**
 * Class ProxyService
 * @package Bavix\Wallet\Services
 * @codeCoverageIgnore
 * @deprecated
 */
class ProxyService implements ArrayAccess
{

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @param string $key
     * @return int
     */
    public function get(string $key): int
    {
        return $this->offsetGet($key);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): int
    {
        return $this->data[$offset] ?? 0;
    }

    /**
     * @param string $key
     * @param int $value
     * @return static
     */
    public function set(string $key, int $value): self
    {
        $this->offsetSet($key, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param string $key
     * @return static
     */
    public function remove(string $key): self
    {
        $this->offsetUnset($key);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * @return void
     */
    public function fresh(): void
    {
        app()->instance(Storable::class, null);
        $this->data = [];
    }

}
