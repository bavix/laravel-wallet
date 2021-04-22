<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Services\UuidFactoryService;

/**
 * @internal
 */
class UuidFactoryServiceTest extends TestCase
{
    public function testUuid4(): void
    {
        /** @var UuidFactoryService $uuidFactoryService */
        $uuidFactoryService = $this->app->get(UuidFactoryService::class);

        self::assertNotSame($uuidFactoryService->uuid4(), $uuidFactoryService->uuid4());
        self::assertMatchesRegularExpression(
            '/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}/',
            $uuidFactoryService->uuid4(),
        );
    }
}
