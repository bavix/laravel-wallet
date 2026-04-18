<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

/**
 * @internal
 */
interface TransactionStateServiceInterface
{
    /**
     * @param non-empty-string $uuid
     * @param array<string, string> $before
     * @param array<string, string> $after
     */
    public function push(string $uuid, int|string $walletId, array $before, array $after): void;

    public function has(string $uuid): bool;

    /**
     * @return array<string, string>
     */
    public function before(string $uuid): array;

    /**
     * @return array<string, string>
     */
    public function after(string $uuid): array;

    /**
     * @return array<non-empty-string, array{walletId: int, before: array<string, string>, after: array<string, string>}>
     */
    public function snapshot(): array;

    /**
     * @param array<non-empty-string, array{walletId: int, before: array<string, string>, after: array<string, string>}> $snapshot
     */
    public function rollback(array $snapshot): void;

    public function reset(): void;
}
