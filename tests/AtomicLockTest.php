<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Internal\Service\LockServiceInterface;

/**
 * @internal
 */
class AtomicLockTest extends TestCase
{
    public function testAtomic(): void
    {
        $atomic = app(LockServiceInterface::class);
        $atomic->block('hello', static fn () => 'hello world');
        $atomic->block('hello', static fn () => 'hello world');
        self::assertTrue(true);
    }
}
