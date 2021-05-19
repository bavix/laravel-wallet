<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Services\BookkeeperService;
use Bavix\Wallet\Services\UuidFactoryService;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * @internal
 */
class BookkeeperServiceTest extends TestCase
{
    public function testBalanceZero(): void
    {
        /** @var BookkeeperService $bookkeeperService */
        $bookkeeperService = $this->app->get(BookkeeperService::class);

        /** @var UuidFactoryService $uuidFactoryService */
        $uuidFactoryService = $this->app->get(UuidFactoryService::class);

        $purseId = $uuidFactoryService->uuid4();

        self::assertSame('0', $bookkeeperService->balance($purseId));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSync(): void
    {
        /** @var BookkeeperService $bookkeeperService */
        $bookkeeperService = $this->app->get(BookkeeperService::class);

        /** @var UuidFactoryService $uuidFactoryService */
        $uuidFactoryService = $this->app->get(UuidFactoryService::class);

        $purseId = $uuidFactoryService->uuid4();

        self::assertTrue($bookkeeperService->sync($purseId, 100));

        self::assertSame('100', $bookkeeperService->balance($purseId));
    }

    public function testBalanceIncrease(): void
    {
        /** @var BookkeeperService $bookkeeperService */
        $bookkeeperService = $this->app->get(BookkeeperService::class);

        /** @var UuidFactoryService $uuidFactoryService */
        $uuidFactoryService = $this->app->get(UuidFactoryService::class);

        $purseId = $uuidFactoryService->uuid4();

        $bookkeeperService->sync($purseId, 0); // init

        self::assertSame('100', $bookkeeperService->increase($purseId, 100));
        self::assertSame('100', $bookkeeperService->balance($purseId));

        self::assertSame('200', $bookkeeperService->increase($purseId, 100));
        self::assertSame('200', $bookkeeperService->balance($purseId));
    }
}
