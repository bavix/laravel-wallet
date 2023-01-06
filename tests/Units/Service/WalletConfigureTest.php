<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Test\Infra\TestCase;
use Bavix\Wallet\WalletConfigure;

/**
 * @internal
 */
final class WalletConfigureTest extends TestCase
{
    public function testIgnoreMigrations(): void
    {
        self::assertTrue(WalletConfigure::isRunsMigrations());

        WalletConfigure::ignoreMigrations();
        self::assertFalse(WalletConfigure::isRunsMigrations());

        WalletConfigure::reset();
        self::assertTrue(WalletConfigure::isRunsMigrations());
    }
}
