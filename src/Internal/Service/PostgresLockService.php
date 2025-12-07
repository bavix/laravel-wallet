<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use function config;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\QueryException;

final class PostgresLockService implements LockServiceInterface
{
    private const string LOCK_KEY = 'wallet_lock::';

    private const string INNER_KEYS = 'inner_keys::';

    private readonly CacheRepository $lockedKeys;

    public function __construct(
        private readonly ConnectionServiceInterface $connectionService,
        private readonly StorageServiceInterface $storageService,
        CacheFactory $cacheFactory,
        private readonly int $seconds
    ) {
        $this->lockedKeys = $cacheFactory->store('array');
    }

    public function block(string $key, callable $callback): mixed
    {
        // Delegate to blocks() with single element array
        return $this->blocks([$key], $callback);
    }

    public function blocks(array $keys, callable $callback): mixed
    {
        // Filter out already blocked keys
        $keysToLock = [];
        foreach ($keys as $key) {
            if (! $this->isBlocked($key)) {
                $keysToLock[] = $key;
            }
        }

        // If all keys are already blocked, just execute callback
        if ($keysToLock === []) {
            return $callback();
        }

        // Sort keys to prevent deadlock
        $sortedKeys = $this->sortKeys($keysToLock);

        // Normalize keys to UUIDs immediately
        // Keys can be in two formats:
        // 1. "wallet_lock::uuid" - full format (from AtomicService, tests)
        // 2. "uuid" - just UUID (from BookkeeperService::multiAmount)
        // 3. Non-UUID keys (e.g., from LockServiceTest using __METHOD__)
        $uuids = [];
        $nonUuidKeys = [];

        foreach ($sortedKeys as $key) {
            // Extract UUID: remove prefix if present, otherwise key is UUID
            $uuid = str_starts_with($key, self::LOCK_KEY)
                ? str_replace(self::LOCK_KEY, '', $key)
                : $key;

            if ($uuid === '') {
                continue;
            }

            // Simple check: UUID format is 36 chars with dashes (8-4-4-4-12)
            // This is a lightweight check without full validation
            if (strlen($uuid) === 36 && substr_count($uuid, '-') === 4) {
                $uuids[] = $uuid;
            } else {
                $nonUuidKeys[] = $key;
            }
        }

        // Handle non-UUID keys: mark as blocked and execute callback without DB query
        foreach ($nonUuidKeys as $key) {
            $this->lockedKeys->put(self::INNER_KEYS.$key, true, $this->seconds);
        }

        $connection = $this->connectionService->get();
        $inTransaction = $connection->transactionLevel() > 0;

        // If no UUIDs found, just execute callback
        // For non-UUID keys inside transaction: keep locked until releases() (like UUID keys)
        // For non-UUID keys outside transaction: clear in finally block
        if ($uuids === []) {
            if ($inTransaction) {
                // Inside transaction: keep locked until releases() is called
                return $callback();
            }

            // Outside transaction: clear after callback
            try {
                return $callback();
            } finally {
                // Clear non-UUID keys after callback (similar to UUID keys in finally block)
                foreach ($nonUuidKeys as $key) {
                    $this->lockedKeys->delete(self::INNER_KEYS.$key);
                }
            }
        }

        if ($inTransaction) {
            // ⚠️ CRITICAL: We are already inside a transaction!
            //
            // This happens in the following scenarios:
            // 1. User created transaction manually (DB::beginTransaction())
            // 2. AtomicService::blocks() created transaction via databaseService->transaction()
            // 3. BookkeeperService::multiAmount() called inside transaction and automatically locks wallet
            //    when record is not found in cache (RecordNotFoundException)
            //
            // AUTOMATIC LOCKING:
            // - When user accesses $wallet->balanceInt inside transaction,
            //   this calls RegulatorService::amount() -> BookkeeperService::amount() -> multiAmount()
            // - If record is not found in cache, BookkeeperService automatically calls
            //   lockService->blocks() to lock the wallet
            // - This means lock can be called INSIDE an existing transaction
            //
            // In this case:
            // - DO NOT create new transaction (we are already inside existing one)
            // - Just set FOR UPDATE lock on existing transaction
            // - Lock will be released automatically by PostgreSQL on commit/rollback
            // - lockedKeys will be cleared via releases() after TransactionCommitted/RolledBack event
            // - If wallets are already locked in this transaction, PostgreSQL will return them anyway
            //   (FOR UPDATE on already locked row in same transaction is safe and returns the row)
            $this->lockWallets($uuids);

            return $callback();
        }

        // PostgresLockService creates transaction
        // Clear lockedKeys after transaction completes to prevent accumulation in Octane
        try {
            return $connection->transaction(function () use ($uuids, $callback) {
                $this->lockWallets($uuids);

                return $callback();
            });
        } finally {
            // CRITICAL for Octane: clear lockedKeys after transaction completes
            // This prevents accumulation in long-lived processes
            foreach ($uuids as $uuid) {
                $this->lockedKeys->delete(self::INNER_KEYS.$uuid);
            }
        }
    }

    public function releases(array $keys): void
    {
        // Called from RegulatorService::purge() after TransactionCommitted/RolledBack
        foreach ($keys as $key) {
            // Normalize key to UUID (we store only UUIDs, not original key format)
            $uuid = str_starts_with($key, self::LOCK_KEY)
                ? str_replace(self::LOCK_KEY, '', $key)
                : $key;

            if ($uuid !== '' && $this->lockedKeys->get(self::INNER_KEYS.$uuid) === true) {
                // Clear lockedKeys - DB locks already released by PostgreSQL
                $this->lockedKeys->delete(self::INNER_KEYS.$uuid);
            }
        }
    }

    public function isBlocked(string $key): bool
    {
        // Normalize key to UUID (we store only UUIDs, not original key format)
        $uuid = str_starts_with($key, self::LOCK_KEY)
            ? str_replace(self::LOCK_KEY, '', $key)
            : $key;

        if ($uuid === '') {
            return false;
        }

        return $this->lockedKeys->get(self::INNER_KEYS.$uuid) === true;
    }

    /**
     * Lock multiple wallets with FOR UPDATE and sync their balances to cache.
     *
     * CRITICAL: This method MUST read balance from DB before locking and sync it to state transaction.
     * The balance is read with FOR UPDATE lock, then synced to StorageService (which uses array cache
     * when PostgresLockService is active). This ensures balance is always fresh from DB within transaction.
     *
     * Optimized: single query for all wallets, single multiSync, single multiGet for verification.
     *
     * @param array<non-empty-string> $uuids Array of normalized UUIDs (already normalized, no prefix)
     */
    private function lockWallets(array $uuids): void
    {
        if ($uuids === []) {
            return;
        }

        // CRITICAL: Read balance from DB with FOR UPDATE lock BEFORE syncing to state transaction
        // This ensures we always have the latest balance from database, not from external cache
        // OPTIMIZATION: Single query to lock all wallets at once
        // SELECT uuid, balance FROM wallets WHERE uuid IN (?, ?, ...) FOR UPDATE
        $connection = $this->connectionService->get();
        $table = config('wallet.wallet.table', 'wallets');
        if (! is_string($table) || $table === '') {
            throw new TransactionFailedException('Invalid wallet table name for locking');
        }

        try {
            $wallets = $connection->table($table)
                ->select(['uuid', 'balance'])
                ->whereIn('uuid', $uuids)
                ->lockForUpdate()
                ->get()
                ->keyBy('uuid');
        } catch (QueryException $e) {
            // Only map invalid UUID format to ModelNotFoundException, rethrow everything else
            $sqlState = $e->errorInfo[0] ?? null;
            if ($sqlState === '22P02') {
                throw new ModelNotFoundException(
                    'Invalid wallet UUID or wallet not found: '.implode(', ', $uuids),
                    ExceptionInterface::MODEL_NOT_FOUND,
                    $e
                );
            }

            throw $e;
        }

        // Extract balances from locked wallets (fresh from DB, not from cache)
        // For wallets not found in DB (lazy creation), use balance 0
        $balances = [];
        foreach ($uuids as $uuid) {
            $wallet = $wallets->get($uuid);
            if ($wallet !== null) {
                $balance = (string) ($wallet->balance ?? '0');
                assert($balance !== '', 'Balance should not be an empty string');
                $balances[$uuid] = $balance;
            } else {
                // Wallet doesn't exist in DB yet (lazy creation) - use balance 0
                // This is normal for new wallets that haven't been saved yet
                $balances[$uuid] = '0';
            }
        }
        // Mark all UUIDs as locked (store only UUID, already normalized)
        foreach ($uuids as $uuid) {
            $this->lockedKeys->put(self::INNER_KEYS.$uuid, true, $this->seconds);
        }

        // CRITICAL: Sync balances to StorageService (state transaction)
        // StorageService uses array cache when PostgresLockService is active,
        // ensuring balance is stored in-memory for the transaction
        // OPTIMIZATION: Single multiSync for all balances
        $this->storageService->multiSync($balances);

        // OPTIMIZATION: Single multiGet to verify all balances at once
        $cachedBalances = $this->storageService->multiGet($uuids);

        // CRITICAL CHECK: Verify cache sync for all wallets
        foreach ($uuids as $uuid) {
            $expectedBalance = $balances[$uuid];
            $cachedBalance = $cachedBalances[$uuid] ?? null;

            if ($cachedBalance !== $expectedBalance) {
                throw new TransactionFailedException(
                    "CRITICAL: Cache sync failed for wallet {$uuid}. ".
                    "Expected: {$expectedBalance}, Got: {$cachedBalance}. ".
                    'This may cause financial inconsistencies!',
                    ExceptionInterface::TRANSACTION_FAILED
                );
            }
        }
    }

    /**
     * @param list<string> $keys
     * @return list<string>
     */
    private function sortKeys(array $keys): array
    {
        // Sort to prevent deadlock
        $sorted = $keys;
        sort($sorted);

        return $sorted;
    }
}
