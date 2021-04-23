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

        self::assertTrue($atomicLockService->acquire(__FUNCTION__));
        self::assertFalse($atomicLockService->acquire(__FUNCTION__));
    }

    public function testRelease(): void
    {
        /** @var AtomicLockService $atomicLockService */
        $atomicLockService = $this->app->get(AtomicLockService::class);

        self::assertFalse($atomicLockService->release(__FUNCTION__));
        self::assertTrue($atomicLockService->acquire(__FUNCTION__));
        self::assertTrue($atomicLockService->release(__FUNCTION__));
    }
}
