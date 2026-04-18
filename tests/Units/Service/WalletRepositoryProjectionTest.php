<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Repository\WalletRepositoryInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class WalletRepositoryProjectionTest extends TestCase
{
    public function testUpdateBalancesProjectsExtraColumnsForBatch(): void
    {
        /** @var Buyer $first */
        $first = BuyerFactory::new()->create();
        /** @var Buyer $second */
        $second = BuyerFactory::new()->create();

        $firstWallet = $first->wallet;
        $secondWallet = $second->wallet;

        $firstWallet->forceFill(['meta' => null])->saveQuietly();
        $secondWallet->forceFill(['meta' => null])->saveQuietly();

        $walletRepository = app(WalletRepositoryInterface::class);
        $walletRepository->updateBalances(
            [
                $firstWallet->getKey() => '111',
                $secondWallet->getKey() => '222',
            ],
            [
                999_999 => ['name' => 'must-be-skipped'],
                $firstWallet->getKey() => ['meta' => '{"projection":true}'],
            ],
        );

        $firstWallet->refresh();
        $secondWallet->refresh();

        self::assertSame(111, $firstWallet->balanceInt);
        self::assertSame(222, $secondWallet->balanceInt);

        self::assertSame('{"projection":true}', $firstWallet->getRawOriginal('meta'));
        self::assertNull($secondWallet->getRawOriginal('meta'));
    }
}
