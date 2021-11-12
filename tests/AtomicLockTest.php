<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Internal\LockInterface;

/**
 * @internal
 * @coversNothing
 */
class AtomicLockTest extends TestCase
{
    public function testAtomic(): void
    {
        $atomic = app(LockInterface::class);
        $atomic->block('hello', static fn () => 'hello world');
        $atomic->block('hello', static fn () => 'hello world');
        self::assertTrue(true);
    }
}
