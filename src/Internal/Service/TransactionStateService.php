<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\StateNotFoundException;

/**
 * @internal This is a helper service for custom transaction state projections.
 *           It is NOT automatically used by core TransactionService.
 *           User can inject it into their custom Assembler via DI.
 */
final class TransactionStateService implements TransactionStateServiceInterface
{
    /**
     * @var array<non-empty-string, array{walletId: int|string, before: array<string, string>, after: array<string, string>}>
     */
    private array $entries = [];

    public function push(string $uuid, int|string $walletId, array $before, array $after): void
    {
        $this->entries[$uuid] = [
            'walletId' => $walletId,
            'before' => $before,
            'after' => $after,
        ];
    }

    public function has(string $uuid): bool
    {
        return array_key_exists($uuid, $this->entries);
    }

    public function before(string $uuid): array
    {
        return $this->get($uuid)['before'];
    }

    public function after(string $uuid): array
    {
        return $this->get($uuid)['after'];
    }

    /**
     * @return array<non-empty-string, array{walletId: int|string, before: array<string, string>, after: array<string, string>}>
     */
    public function snapshot(): array
    {
        return $this->entries;
    }

    /**
     * @param array<non-empty-string, array{walletId: int|string, before: array<string, string>, after: array<string, string>}> $snapshot
     */
    public function rollback(array $snapshot): void
    {
        $this->entries = $snapshot;
    }

    public function reset(): void
    {
        $this->entries = [];
    }

    /**
     * @return array{walletId: int|string, before: array<string, string>, after: array<string, string>}
     */
    private function get(string $uuid): array
    {
        if (! array_key_exists($uuid, $this->entries)) {
            throw new StateNotFoundException('State entry not found: '.$uuid);
        }

        return $this->entries[$uuid];
    }
}
