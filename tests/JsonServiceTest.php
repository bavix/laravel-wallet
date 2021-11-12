<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Internal\Service\JsonService;

/**
 * @internal
 */
final class JsonServiceTest extends TestCase
{
    public function testJsonEncodeSuccess(): void
    {
        $jsonService = app(JsonService::class);
        self::assertJson($jsonService->encode([1]));
        self::assertNull($jsonService->encode(null));
    }

    public function testJsonEncodeFailed(): void
    {
        $jsonService = app(JsonService::class);
        $array = [1];
        $array[] = &$array;

        self::assertNull($jsonService->encode($array));
    }
}
