<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Wallet;
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

        // Extract UUIDs from keys
        // Keys can be in two formats:
        // 1. "wallet_lock::uuid" - full format (from AtomicService, tests)
        // 2. "uuid" - just UUID (from BookkeeperService::multiAmount)
        $uuids = [];
        foreach ($sortedKeys as $key) {
            $uuid = $this->extractUuid($key);
            // UUID should not be empty - if it is, it will cause ModelNotFoundException
            if ($uuid !== '') {
                $uuids[$key] = $uuid;
            }
        }

        // If no valid UUIDs found, throw exception
        if ($uuids === []) {
            throw new ModelNotFoundException(
                'No valid wallet UUIDs found in lock keys',
                ExceptionInterface::MODEL_NOT_FOUND
            );
        }

        $connection = $this->connectionService->get();
        $inTransaction = $connection->transactionLevel() > 0;

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
            $this->lockWallets($uuids, $sortedKeys);

            return $callback();
        }

        // PostgresLockService creates transaction
        // Clear lockedKeys after transaction completes to prevent accumulation in Octane
        try {
            return $connection->transaction(function () use ($uuids, $sortedKeys, $callback) {
                $this->lockWallets($uuids, $sortedKeys);
                return $callback();
            });
        } finally {
            // CRITICAL for Octane: clear lockedKeys after transaction completes
            // This prevents accumulation in long-lived processes
            // Clear both original key and normalized UUID formats
            foreach ($sortedKeys as $key) {
                $this->lockedKeys->delete(self::INNER_KEYS.$key);
                $uuid = $this->extractUuid($key);
                if ($uuid !== '' && $uuid !== $key) {
                    $this->lockedKeys->delete(self::INNER_KEYS.$uuid);
                }
            }
        }
    }

    public function releases(array $keys): void
    {
        // Called from RegulatorService::purge() after TransactionCommitted/RolledBack
        foreach ($keys as $key) {
            if ($this->isBlocked($key)) {
                // Clear lockedKeys - DB locks already released by PostgreSQL
                // Delete both original key and normalized UUID
                $this->lockedKeys->delete(self::INNER_KEYS.$key);
                $uuid = $this->extractUuid($key);
                if ($uuid !== '' && $uuid !== $key) {
                    $this->lockedKeys->delete(self::INNER_KEYS.$uuid);
                }
            }
        }
    }

    public function isBlocked(string $key): bool
    {
        // Normalize key - extract UUID if key has prefix
        $normalizedKey = $this->extractUuid($key);
        // If extraction failed (empty), use original key
        if ($normalizedKey === '') {
            $normalizedKey = $key;
        }
        // Check both formats: with prefix and without
        if ($this->lockedKeys->get(self::INNER_KEYS.$key) === true) {
            return true;
        }

        return $this->lockedKeys->get(self::INNER_KEYS.$normalizedKey) === true;
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
     * @param array<string, string> $uuids Array of key => uuid pairs
     * @param string[] $keys Array of lock keys
     */
    private function lockWallets(array $uuids, array $keys): void
    {
        if ($uuids === []) {
            return;
        }

        // CRITICAL: Read balance from DB with FOR UPDATE lock BEFORE syncing to state transaction
        // This ensures we always have the latest balance from database, not from external cache
        // OPTIMIZATION: Single query to lock all wallets at once
        // SELECT * FROM wallets WHERE uuid IN (?, ?, ...) FOR UPDATE
        $uuidList = array_values($uuids);
        
        try {
            $wallets = Wallet::query()
                ->whereIn('uuid', $uuidList)
                ->lockForUpdate()
                ->get()
                ->keyBy('uuid');
        } catch (QueryException $e) {
            // PostgreSQL throws QueryException for invalid UUID format
            // Convert it to ModelNotFoundException for consistency
            throw new ModelNotFoundException(
                'Invalid wallet UUID or wallet not found: '.implode(', ', $uuidList),
                ExceptionInterface::MODEL_NOT_FOUND,
                $e
            );
        }

        // Check if all wallets were found
        // Note: If wallet is already locked in this transaction, PostgreSQL will still return it
        // (FOR UPDATE on already locked row in same transaction is safe and returns the row)
        // So if it's missing, it truly doesn't exist
        $foundUuids = $wallets->keys()
            ->all();
        $missingUuids = array_diff($uuidList, $foundUuids);
        
        if ($missingUuids !== []) {
            throw new ModelNotFoundException(
                'Wallets not found: '.implode(', ', $missingUuids),
                ExceptionInterface::MODEL_NOT_FOUND
            );
        }

        // Extract balances from locked wallets (fresh from DB, not from cache)
        $balances = [];
        foreach ($uuidList as $uuid) {
            $wallet = $wallets->get($uuid);
            if ($wallet === null) {
                throw new ModelNotFoundException("Wallet not found: {$uuid}", ExceptionInterface::MODEL_NOT_FOUND);
            }
            $balances[$uuid] = $wallet->getOriginalBalanceAttribute();
        }

        // Mark all keys as locked (single operation per key, but done in batch)
        // Normalize keys to UUID format for consistent storage
        foreach ($keys as $key) {
            $uuid = $this->extractUuid($key);
            $normalizedKey = $uuid !== '' ? $uuid : $key;
            // Store both original key and normalized UUID for compatibility
            $this->lockedKeys->put(self::INNER_KEYS.$key, true, $this->seconds);
            if ($normalizedKey !== $key) {
                $this->lockedKeys->put(self::INNER_KEYS.$normalizedKey, true, $this->seconds);
            }
        }

        // CRITICAL: Sync balances to StorageService (state transaction)
        // StorageService uses array cache when PostgresLockService is active,
        // ensuring balance is stored in-memory for the transaction
        // OPTIMIZATION: Single multiSync for all balances
        $this->storageService->multiSync($balances);

        // OPTIMIZATION: Single multiGet to verify all balances at once
        $cachedBalances = $this->storageService->multiGet($uuidList);

        // CRITICAL CHECK: Verify cache sync for all wallets
        foreach ($uuidList as $uuid) {
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

    private function extractUuid(string $key): string
    {
        // Extract UUID from key
        // Keys can be in two formats:
        // 1. "wallet_lock::uuid" - full format (from AtomicService, tests)
        // 2. "uuid" - just UUID (from BookkeeperService::multiAmount)
        // Remove prefix if present, otherwise return key as-is (assuming it's a UUID)
        if (str_starts_with($key, self::LOCK_KEY)) {
            return str_replace(self::LOCK_KEY, '', $key);
        }

        // Key is already a UUID (from BookkeeperService)
        return $key;
    }

    private function sortKeys(array $keys): array
    {
        // Sort to prevent deadlock
        $sorted = $keys;
        sort($sorted);

        return $sorted;
    }
}
