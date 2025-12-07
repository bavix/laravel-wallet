<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Internal\Service\PostgresLockService;
use Bavix\Wallet\Services\BookkeeperServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\UserFactory;
use Bavix\Wallet\Test\Infra\Models\User;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Support\Facades\DB;

/**
 * @internal
 */
final class PostgresLockServiceTest extends TestCase
{
    public function testBlockSingleWallet(): void
    {
        $this->skipIfNotPostgresLockService();

        /** @var User $user */
        $user = UserFactory::new()->create();
        $user->deposit(1000);

        $lock = app(LockServiceInterface::class);
        self::assertInstanceOf(PostgresLockService::class, $lock);

        $key = 'wallet_lock::'.$user->wallet->uuid;
        self::assertFalse($lock->isBlocked($key));

        $result = $lock->block($key, static fn () => 'test');
        self::assertSame('test', $result);
        self::assertFalse($lock->isBlocked($key));
    }

    public function testBlocksMultipleWallets(): void
    {
        $this->skipIfNotPostgresLockService();

        $users = UserFactory::times(3)->create()->all();
        /** @var array{0: User, 1: User, 2: User} $users */
        [$user1, $user2, $user3] = $users;

        $user1->deposit(1000);
        $user2->deposit(2000);
        $user3->deposit(3000);

        $lock = app(LockServiceInterface::class);
        self::assertInstanceOf(PostgresLockService::class, $lock);

        $keys = [
            'wallet_lock::'.$user1->wallet->uuid,
            'wallet_lock::'.$user2->wallet->uuid,
            'wallet_lock::'.$user3->wallet->uuid,
        ];

        foreach ($keys as $key) {
            self::assertFalse($lock->isBlocked($key));
        }

        $result = $lock->blocks($keys, static fn () => 'test');
        self::assertSame('test', $result);

        foreach ($keys as $key) {
            self::assertFalse($lock->isBlocked($key));
        }
    }

    public function testBlockInTransaction(): void
    {
        $this->skipIfNotPostgresLockService();

        /** @var User $user */
        $user = UserFactory::new()->create();
        $user->deposit(1000);

        $lock = app(LockServiceInterface::class);
        $key = 'wallet_lock::'.$user->wallet->uuid;

        DB::beginTransaction();

        $checkIsBlock = $lock->block($key, static fn () => $lock->isBlocked($key));
        self::assertTrue($checkIsBlock);
        self::assertTrue($lock->isBlocked($key));

        DB::commit();

        // After commit, lockedKeys should still be set until releases() is called
        self::assertTrue($lock->isBlocked($key));

        $lock->releases([$key]);
        self::assertFalse($lock->isBlocked($key));
    }

    public function testBlocksInTransaction(): void
    {
        $this->skipIfNotPostgresLockService();

        $users = UserFactory::times(2)->create()->all();
        /** @var array{0: User, 1: User} $users */
        [$user1, $user2] = $users;

        $user1->deposit(1000);
        $user2->deposit(2000);

        $lock = app(LockServiceInterface::class);
        $keys = ['wallet_lock::'.$user1->wallet->uuid, 'wallet_lock::'.$user2->wallet->uuid];

        DB::beginTransaction();

        $checkIsBlock1 = $lock->blocks($keys, static fn () => $lock->isBlocked($keys[0]));
        self::assertTrue($checkIsBlock1);
        self::assertTrue($lock->isBlocked($keys[0]));
        self::assertTrue($lock->isBlocked($keys[1]));

        DB::commit();

        self::assertTrue($lock->isBlocked($keys[0]));
        self::assertTrue($lock->isBlocked($keys[1]));

        $lock->releases($keys);
        self::assertFalse($lock->isBlocked($keys[0]));
        self::assertFalse($lock->isBlocked($keys[1]));
    }

    public function testAutomaticLockingViaBookkeeperService(): void
    {
        $this->skipIfNotPostgresLockService();

        /** @var User $user */
        $user = UserFactory::new()->create();
        $user->deposit(1000);

        // Clear cache to trigger automatic locking
        $bookkeeper = app(BookkeeperServiceInterface::class);
        $bookkeeper->forget($user->wallet);

        DB::beginTransaction();

        // Accessing balance should trigger automatic locking
        // BookkeeperService::multiAmount() calls lockService->blocks() with UUID (not wallet_lock::uuid)
        $balance = $user->wallet->balanceInt;
        self::assertSame(1000, $balance);

        $lock = app(LockServiceInterface::class);
        // BookkeeperService uses UUID as key, not wallet_lock::uuid
        $key = $user->wallet->uuid;

        // Lock should be set after accessing balance
        self::assertTrue($lock->isBlocked($key));

        DB::commit();

        $lock->releases([$key]);
        self::assertFalse($lock->isBlocked($key));
    }

    public function testReleases(): void
    {
        $this->skipIfNotPostgresLockService();

        $users = UserFactory::times(2)->create()->all();
        /** @var array{0: User, 1: User} $users */
        [$user1, $user2] = $users;

        // Ensure wallets are created in database before transaction
        $user1->deposit(0);
        $user2->deposit(0);

        $lock = app(LockServiceInterface::class);
        $keys = ['wallet_lock::'.$user1->wallet->uuid, 'wallet_lock::'.$user2->wallet->uuid];

        DB::beginTransaction();

        $lock->blocks($keys, static fn () => null);

        self::assertTrue($lock->isBlocked($keys[0]));
        self::assertTrue($lock->isBlocked($keys[1]));

        DB::commit();

        $lock->releases($keys);

        self::assertFalse($lock->isBlocked($keys[0]));
        self::assertFalse($lock->isBlocked($keys[1]));
    }

    public function testBlockedKeyPreventsDoubleLock(): void
    {
        $this->skipIfNotPostgresLockService();

        /** @var User $user */
        $user = UserFactory::new()->create();

        // Ensure wallet is created in database before transaction
        $user->deposit(0);

        $lock = app(LockServiceInterface::class);
        $key = 'wallet_lock::'.$user->wallet->uuid;

        DB::beginTransaction();

        // First lock
        $lock->block($key, static fn () => null);
        self::assertTrue($lock->isBlocked($key));

        // Second lock should not create new transaction, just execute callback
        $result = $lock->block($key, static fn () => 'already locked');
        self::assertSame('already locked', $result);
        self::assertTrue($lock->isBlocked($key));

        DB::commit();

        $lock->releases([$key]);
        self::assertFalse($lock->isBlocked($key));
    }

    public function testCacheSyncAfterLock(): void
    {
        $this->skipIfNotPostgresLockService();

        /** @var User $user */
        $user = UserFactory::new()->create();
        $user->deposit(1000);

        $lock = app(LockServiceInterface::class);
        $key = 'wallet_lock::'.$user->wallet->uuid;

        // Lock should sync balance to cache
        $lock->block($key, static fn () => null);

        // Balance should be accessible from cache
        $balance = $user->wallet->balanceInt;
        self::assertSame(1000, $balance);
    }

    public function testMultipleWalletsCacheSync(): void
    {
        $this->skipIfNotPostgresLockService();

        $users = UserFactory::times(2)->create()->all();
        /** @var array{0: User, 1: User} $users */
        [$user1, $user2] = $users;

        $user1->deposit(1000);
        $user2->deposit(2000);

        $lock = app(LockServiceInterface::class);
        $keys = ['wallet_lock::'.$user1->wallet->uuid, 'wallet_lock::'.$user2->wallet->uuid];

        // Lock should sync all balances to cache
        $lock->blocks($keys, static fn () => null);

        // Balances should be accessible from cache
        self::assertSame(1000, $user1->wallet->balanceInt);
        self::assertSame(2000, $user2->wallet->balanceInt);
    }

    /**
     * Skip test if PostgresLockService conditions are not met.
     * Checks: database = pgsql AND lock.driver = database AND PostgresLockService is used.
     */
    private function skipIfNotPostgresLockService(): void
    {
        // Check database driver
        $dbDriver = config('database.default');
        if (! is_string($dbDriver) || $dbDriver === '') {
            $dbDriver = 'database';
        }

        $dbDriverActual = config('database.connections.'.$dbDriver.'.driver');
        if (! is_string($dbDriverActual) || $dbDriverActual === '') {
            $dbDriverActual = 'unknown';
        }

        if ($dbDriver !== 'pgsql' || $dbDriverActual !== 'pgsql') {
            $this->markTestSkipped(
                'PostgresLockService tests require PostgreSQL database (pgsql). Current: '.$dbDriver.'/'.$dbDriverActual
            );
        }

        // Check lock driver
        /** @var string $lockDriver */
        $lockDriver = config('wallet.lock.driver', '');
        if ($lockDriver !== 'database') {
            $this->markTestSkipped(
                'PostgresLockService tests require wallet.lock.driver = database. Current: '.$lockDriver
            );
        }

        // Verify that PostgresLockService is actually used
        $lock = app(LockServiceInterface::class);
        if (! ($lock instanceof PostgresLockService)) {
            $this->markTestSkipped('PostgresLockService is not being used. LockService: '.get_class($lock));
        }
    }
}
