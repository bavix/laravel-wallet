<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;

interface StorageServiceInterface
{
    /**
     * Flushes all the stored values.
     *
     * This method clears all the stored values, effectively removing them from the storage.
     * The method returns a boolean value indicating whether the flush operation was successful
     * or not.
     *
     * @return bool True if the flush operation was successful, false otherwise.
     */
    public function flush(): bool;

    /**
     * Forgets the stored value for the given UUID.
     *
     * This method removes the stored value associated with the provided UUID from the storage.
     *
     * @param non-empty-string $uuid The UUID of the stored value to forget.
     * @return bool True if the value was successfully forgotten, false otherwise.
     */
    public function forget(string $uuid): bool;

    /**
     * Retrieves the stored value for the given UUID.
     *
     * This method retrieves the stored value associated with the provided UUID from the storage.
     * If the value with the given UUID does not exist, a `RecordNotFoundException` is thrown.
     *
     * @param non-empty-string $uuid The UUID of the stored value.
     * @return non-empty-string The stored value.
     *
     * @throws RecordNotFoundException If the value with the given UUID is not found.
     */
    public function get(string $uuid): string;

    /**
     * Synchronizes the stored value for the given UUID.
     *
     * This method updates the stored value associated with the provided UUID with the specified value.
     * If the value does not exist, it will be created. If the value already exists, it will be updated.
     *
     * @param non-empty-string $uuid The UUID of the stored value.
     * @param float|int|non-empty-string $value The value to synchronize.
     * @return bool Returns `true` if the synchronization was successful, `false` otherwise.
     */
    public function sync(string $uuid, float|int|string $value): bool;

    /**
     * Increases the stored value for the given UUID by the specified amount.
     *
     * This method increases the stored value associated with the provided UUID by the specified amount.
     * If the value with the given UUID does not exist, a `RecordNotFoundException` is thrown.
     *
     * @param non-empty-string $uuid The UUID of the stored value.
     * @param float|int|non-empty-string $value The amount to increase the stored value by.
     * @return non-empty-string The updated stored value.
     *
     * @throws RecordNotFoundException If the value with the given UUID is not found.
     */
    public function increase(string $uuid, float|int|string $value): string;

    /**
     * Retrieves the stored values for the given UUIDs.
     *
     * This method retrieves the stored values associated with the provided UUIDs from the storage.
     * If any of the values with the given UUIDs do not exist, a `RecordNotFoundException` is thrown.
     *
     * @param non-empty-array<non-empty-string> $uuids The UUIDs of the stored values.
     * @return non-empty-array<non-empty-string, non-empty-string> The stored values. The keys are the UUIDs and the values are the corresponding
     *                              stored values.
     *
     * @throws RecordNotFoundException If any of the values with the given UUIDs are not found.
     */
    public function multiGet(array $uuids): array;

    /**
     * Synchronizes multiple stored values at once.
     *
     * This method updates the stored values associated with the provided UUIDs with the specified values.
     * If any of the values with the given UUIDs do not exist, a `RecordNotFoundException` is thrown.
     *
     * @param non-empty-array<non-empty-string, float|int|non-empty-string> $inputs An associative array
     *                                                          where the keys are UUIDs and the values are the corresponding
     *                                                          stored values.
     * @return bool Returns `true` if the synchronization was successful, `false` otherwise.
     *
     * @throws RecordNotFoundException If any of the values with the given UUIDs are not found.
     */
    public function multiSync(array $inputs): bool;

    /**
     * Increase multiple stored values at once.
     *
     * This method takes an associative array where the keys are UUIDs and the values are the amounts to increase the
     * corresponding stored values by.
     *
     * @template T of non-empty-array<non-empty-string, float|int|non-empty-string>
     *
     * @param T $inputs An associative array where the keys are UUIDs and the values are the amounts to increase the
     *                  corresponding stored values by.
     * @return non-empty-array<key-of<T>, non-empty-string> An associative array where the keys are the UUIDs and the values are the
     *                                           updated stored values.
     *
     * @throws RecordNotFoundException If any of the values with the given UUIDs are not found.
     */
    public function multiIncrease(array $inputs): array;
}
