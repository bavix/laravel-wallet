<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use function app;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\BookkeeperServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;
use PDOException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
class BalanceTest extends TestCase
{
    public function testDepositWalletExists(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $buyer->deposit(1);

        self::assertTrue($buyer->relationLoaded('wallet'));
        self::assertTrue($buyer->wallet->exists);
    }

    public function testCheckType(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $buyer->deposit(1000);

        self::assertIsString($buyer->balance);
        self::assertIsString($buyer->wallet->balanceFloat);

        self::assertIsInt($buyer->balanceInt);

        self::assertSame('1000', $buyer->balance);
        self::assertSame('10.00', $buyer->wallet->balanceFloat);

        self::assertSame(1000, $buyer->balanceInt);
    }

    public function testCanWithdraw(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertTrue($buyer->canWithdraw(0));

        $buyer->forceWithdraw(1);
        self::assertFalse($buyer->canWithdraw(0));
        self::assertTrue($buyer->canWithdraw(0, true));
    }

    public function testWithdrawWalletExists(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        self::assertSame($buyer->balanceInt, 0);
        $buyer->forceWithdraw(1);

        self::assertSame($buyer->balanceInt, -1);
        self::assertTrue($buyer->relationLoaded('wallet'));
        self::assertTrue($buyer->wallet->exists);
        self::assertLessThan(0, $buyer->balanceInt);
    }

    /**
     * @throws
     */
    public function testSimple(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        self::assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        self::assertFalse($wallet->exists);
        self::assertSame(0, $wallet->balanceInt);
        self::assertTrue($wallet->exists);

        $wallet->deposit(1000);
        self::assertSame(1000, $wallet->balanceInt);

        $regulator = app(RegulatorServiceInterface::class);
        $result = $regulator->increase($wallet, 100);

        self::assertSame(100, (int) $regulator->diff($wallet));
        self::assertSame(1100, (int) $regulator->amount($wallet));
        self::assertSame(1100, (int) $result);

        self::assertSame(1100, $wallet->balanceInt);
        self::assertTrue($wallet->refreshBalance());

        self::assertSame(0, (int) $regulator->diff($wallet));
        self::assertSame(1000, (int) $regulator->amount($wallet));
        self::assertSame(1000, $wallet->balanceInt);

        $key = $wallet->getKey();
        self::assertTrue($wallet->delete());
        self::assertFalse($wallet->exists);
        self::assertSame($wallet->getKey(), $key);
        $result = app(RegulatorServiceInterface::class)->increase($wallet, 100);

        // databases that do not support fk will not delete data... need to help them
        $wallet->transactions()->where('wallet_id', $key)->delete();

        self::assertFalse($wallet->exists);
        self::assertSame(1100, (int) $result);

        $wallet->refreshBalance(); // automatic create default wallet
        self::assertTrue($wallet->exists);

        self::assertSame(0, $wallet->balanceInt);

        $wallet->deposit(1);
        self::assertSame(1, $wallet->balanceInt);
    }

    /**
     * @throws
     */
    public function testGetBalance(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        self::assertFalse($wallet->exists);
        self::assertSame($wallet->balanceInt, 0);
        self::assertTrue($wallet->exists);

        self::assertSame('0', app(BookkeeperServiceInterface::class)->amount($wallet));
    }

    /**
     * @throws
     */
    public function testThrowUpdate(): void
    {
        $this->expectException(PDOException::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        self::assertFalse($wallet->exists);
        self::assertSame(0, $wallet->balanceInt);
        self::assertTrue($wallet->exists);

        /** @var MockObject|Wallet $mockQuery */
        $mockQuery = $this->createMock(\get_class($wallet->newQuery()));
        $mockQuery->method('whereKey')->willReturn($mockQuery);
        $mockQuery->method('update')->willThrowException(new PDOException());

        /** @var MockObject|Wallet $mockWallet */
        $mockWallet = $this->createMock(\get_class($wallet));
        $mockWallet->method('getBalanceAttribute')->willReturn('125');
        $mockWallet->method('newQuery')->willReturn($mockQuery);
        $mockWallet->method('getKey')->willReturn($wallet->getKey());

        $mockWallet->newQuery()->whereKey($wallet->getKey())->update(['balance' => 100]);
    }

    public function testEqualWallet(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertSame(0, $wallet->balanceInt);

        $wallet->deposit(1000);
        self::assertSame(1000, $wallet->balanceInt);
        self::assertSame(1000, $wallet->wallet->balanceInt);
        self::assertSame($wallet->getKey(), $wallet->wallet->getKey());
        self::assertSame($wallet->getKey(), $wallet->wallet->wallet->getKey());
        self::assertSame($wallet->getKey(), $wallet->wallet->wallet->wallet->getKey());
    }
}
