<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
class LockServiceTest extends TestCase
{
    public function testBlock(): void
    {
        $lock = app(LockServiceInterface::class);
        $lock->block('hello', static fn () => 'hello world');
        $lock->block('hello', static fn () => 'hello world');
        self::assertTrue(true);
    }
}
