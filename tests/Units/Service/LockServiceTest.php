<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Support\Facades\DB;

/**
 * @internal
 */
final class LockServiceTest extends TestCase
{
    public function testBlock(): void
    {
        $blockKey = __METHOD__;
        $lock = app(LockServiceInterface::class);

        $message = $lock->block($blockKey, static fn () => 'hello world');
        self::assertSame('hello world', $message);

        $message = $lock->block($blockKey, static fn () => 'hello world');
        self::assertSame('hello world', $message);

        self::assertTrue(true);
    }

    public function testLockFailed(): void
    {
        $blockKey = __METHOD__;
        $lock = app(LockServiceInterface::class);

        try {
            $lock->block($blockKey, static fn () => throw new \Exception('hello world'));
        } catch (\Throwable $throwable) {
            self::assertSame('hello world', $throwable->getMessage());
        }

        $message = $lock->block($blockKey, static fn () => 'hello world');
        self::assertSame('hello world', $message);
        self::assertTrue(true);
    }

    public function testLockDeep(): void
    {
        $blockKey = __METHOD__;
        $lock = app(LockServiceInterface::class);
        self::assertFalse($lock->isBlocked($blockKey));

        $message = $lock->block(
            $blockKey,
            static fn () => $lock->block($blockKey, static fn () => 'hello world'),
        );

        self::assertSame('hello world', $message);
        self::assertTrue(true);

        self::assertFalse($lock->isBlocked($blockKey));

        $checkIsBlock = $lock->block($blockKey, static fn () => $lock->isBlocked($blockKey));

        self::assertTrue($checkIsBlock);
        self::assertFalse($lock->isBlocked($blockKey));
    }

    public function testInTransactionLockable(): void
    {
        $blockKey1 = __METHOD__ . '1';
        $blockKey2 = __METHOD__ . '2';
        $lock = app(LockServiceInterface::class);
        self::assertFalse($lock->isBlocked($blockKey1));
        self::assertFalse($lock->isBlocked($blockKey2));

        $checkIsBlock1 = $lock->block($blockKey1, static fn () => $lock->isBlocked($blockKey1));
        self::assertTrue($checkIsBlock1);
        self::assertFalse($lock->isBlocked($blockKey1));
        self::assertFalse($lock->isBlocked($blockKey2));

        $checkIsBlock2 = $lock->block($blockKey2, static fn () => $lock->isBlocked($blockKey2));
        self::assertTrue($checkIsBlock2);
        self::assertFalse($lock->isBlocked($blockKey1));
        self::assertFalse($lock->isBlocked($blockKey2));

        DB::beginTransaction();

        $checkIsBlock1 = $lock->block($blockKey1, static fn () => $lock->isBlocked($blockKey1));
        self::assertTrue($checkIsBlock1);
        self::assertTrue($lock->isBlocked($blockKey1));
        self::assertFalse($lock->isBlocked($blockKey2));

        $checkIsBlock2 = $lock->block($blockKey2, static fn () => $lock->isBlocked($blockKey2));
        self::assertTrue($checkIsBlock2);
        self::assertTrue($lock->isBlocked($blockKey1));
        self::assertTrue($lock->isBlocked($blockKey2));

        DB::commit();

        self::assertTrue($lock->isBlocked($blockKey1));
        self::assertTrue($lock->isBlocked($blockKey2));

        $lock->releases([$blockKey1, $blockKey2]);

        self::assertFalse($lock->isBlocked($blockKey1));
        self::assertFalse($lock->isBlocked($blockKey2));
    }
}
