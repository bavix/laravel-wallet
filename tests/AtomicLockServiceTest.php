<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Services\AtomicLockService;

/**
 * @internal
 */
class AtomicLockServiceTest extends TestCase
{
    public function testAcquire(): void
    {
        /** @var AtomicLockService $atomicLockService */
        $atomicLockService = $this->app->get(AtomicLockService::class);

        self::assertTrue($atomicLockService->acquire('hello'));
        self::assertFalse($atomicLockService->acquire('hello'));
    }

    public function testRelease(): void
    {
        /** @var AtomicLockService $atomicLockService */
        $atomicLockService = $this->app->get(AtomicLockService::class);

        self::assertFalse($atomicLockService->release('hello'));
        self::assertTrue($atomicLockService->acquire('hello'));
        self::assertTrue($atomicLockService->release('hello'));
    }
}
