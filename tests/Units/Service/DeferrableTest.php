<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Test\Infra\TestCase;
use Bavix\Wallet\WalletServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

/**
 * @internal
 */
final class DeferrableTest extends TestCase
{
    public function testCheckDeferrableProvider(): void
    {
        $walletServiceProvider = app()
            ->resolveProvider(WalletServiceProvider::class)
        ;

        self::assertInstanceOf(DeferrableProvider::class, $walletServiceProvider);
        self::assertNotEmpty($walletServiceProvider->provides());
    }
}
