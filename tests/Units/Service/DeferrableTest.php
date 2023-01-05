<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Test\Infra\TestCase;
use Bavix\Wallet\WalletConfigure;
use Bavix\Wallet\WalletServiceProvider;

/**
 * @internal
 */
final class DeferrableTest extends TestCase
{
    public function testIgnoreMigrations(): void
    {
        self::assertNotEmpty(app()->resolveProvider(WalletServiceProvider::class)->provides());
    }
}
