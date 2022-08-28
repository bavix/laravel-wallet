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
        $lock = app(LockServiceInterface::class);

        $message = $lock->block(__METHOD__, static fn () => 'hello world');
        self::assertSame('hello world', $message);

        $message = $lock->block(__METHOD__, static fn () => 'hello world');
        self::assertSame('hello world', $message);

        self::assertTrue(true);
    }

    public function testLockFailed(): void
    {
        $lock = app(LockServiceInterface::class);

        try {
            $lock->block(__METHOD__, static fn () => throw new \Exception('hello world'));
        } catch (\Throwable $throwable) {
            self::assertSame('hello world', $throwable->getMessage());
        }

        $message = $lock->block(__METHOD__, static fn () => 'hello world');
        self::assertSame('hello world', $message);
        self::assertTrue(true);
    }

    public function testLockDeep(): void
    {
        $lock = app(LockServiceInterface::class);
        $message = $lock->block(
            __METHOD__,
            static fn () => $lock->block(__METHOD__, static fn () => 'hello world'),
        );

        self::assertSame('hello world', $message);
        self::assertTrue(true);
    }
}
