<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
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
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configure to use PostgresLockService
        // Only test if database is PostgreSQL
        $driver = config('database.connections.'.config('database.default').'.driver');
        if ($driver !== 'pgsql') {
            $this->markTestSkipped('PostgresLockService tests require PostgreSQL database');
        }
        
        // Set lock driver to database to trigger PostgresLockService
        config(['wallet.lock.driver' => 'database']);
    }

    public function testBlockSingleWallet(): void
    {
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
        /** @var User $user1 */
        /** @var User $user2 */
        /** @var User $user3 */
        [$user1, $user2, $user3] = UserFactory::times(3)->create();
        
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
        /** @var User $user1 */
        /** @var User $user2 */
        [$user1, $user2] = UserFactory::times(2)->create();
        
        $user1->deposit(1000);
        $user2->deposit(2000);
        
        $lock = app(LockServiceInterface::class);
        $keys = [
            'wallet_lock::'.$user1->wallet->uuid,
            'wallet_lock::'.$user2->wallet->uuid,
        ];
        
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
        /** @var User $user */
        $user = UserFactory::new()->create();
        $user->deposit(1000);
        
        // Clear cache to trigger automatic locking
        $bookkeeper = app(BookkeeperServiceInterface::class);
        $bookkeeper->forget($user->wallet);
        
        DB::beginTransaction();
        
        // Accessing balance should trigger automatic locking
        $balance = $user->wallet->balanceInt;
        self::assertSame(1000, $balance);
        
        $lock = app(LockServiceInterface::class);
        $key = 'wallet_lock::'.$user->wallet->uuid;
        
        // Lock should be set after accessing balance
        self::assertTrue($lock->isBlocked($key));
        
        DB::commit();
        
        $lock->releases([$key]);
        self::assertFalse($lock->isBlocked($key));
    }

    public function testReleases(): void
    {
        /** @var User $user1 */
        /** @var User $user2 */
        [$user1, $user2] = UserFactory::times(2)->create();
        
        $lock = app(LockServiceInterface::class);
        $keys = [
            'wallet_lock::'.$user1->wallet->uuid,
            'wallet_lock::'.$user2->wallet->uuid,
        ];
        
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
        /** @var User $user */
        $user = UserFactory::new()->create();
        
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

    public function testNonExistentWalletThrowsException(): void
    {
        $lock = app(LockServiceInterface::class);
        $key = 'wallet_lock::non-existent-uuid';
        
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionCode(ExceptionInterface::MODEL_NOT_FOUND);
        
        $lock->block($key, static fn () => null);
    }

    public function testCacheSyncAfterLock(): void
    {
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
        /** @var User $user1 */
        /** @var User $user2 */
        [$user1, $user2] = UserFactory::times(2)->create();
        
        $user1->deposit(1000);
        $user2->deposit(2000);
        
        $lock = app(LockServiceInterface::class);
        $keys = [
            'wallet_lock::'.$user1->wallet->uuid,
            'wallet_lock::'.$user2->wallet->uuid,
        ];
        
        // Lock should sync all balances to cache
        $lock->blocks($keys, static fn () => null);
        
        // Balances should be accessible from cache
        self::assertSame(1000, $user1->wallet->balanceInt);
        self::assertSame(2000, $user2->wallet->balanceInt);
    }
}

