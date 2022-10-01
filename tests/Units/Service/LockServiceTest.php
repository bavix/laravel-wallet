<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Test\Infra\TestCase;

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
}
