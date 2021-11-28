<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use function app;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\BookkeeperServiceInterface;
use Bavix\Wallet\Services\CommonServiceLegacy;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Database\SQLiteConnection;
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

        $result = app(CommonServiceLegacy::class)->addBalance($wallet, 100);
        self::assertTrue($result);

        self::assertSame(1100, $wallet->balanceInt);
        self::assertTrue($wallet->refreshBalance());

        self::assertSame(1000, $wallet->balanceInt);

        $key = $wallet->getKey();
        self::assertTrue($wallet->delete());
        self::assertFalse($wallet->exists);
        self::assertSame($wallet->getKey(), $key);
        $result = app(CommonServiceLegacy::class)->addBalance($wallet, 100);
        self::assertTrue($result); // automatic create default wallet

        $wallet->refreshBalance();
        $balance = 0;
        if ($wallet->getConnection() instanceof SQLiteConnection) {
            $balance = 1000;
        }

        self::assertSame($wallet->balanceInt, (int) $balance);

        $wallet->deposit(1);
        self::assertSame($wallet->balanceInt, (int) $balance + 1);
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

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/49
     */
    public function testForceUpdate(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertSame(0, $wallet->balanceInt);

        $wallet->deposit(1000);
        self::assertSame(1000, $wallet->balanceInt);
        self::assertSame(0, (int) app(RegulatorServiceInterface::class)->diff($wallet));

        Wallet::whereKey($buyer->wallet->getKey())
            ->update(['balance' => 10])
        ;

        /**
         * Create a state when the cache is empty.
         * For example, something went wrong and your database has incorrect data.
         * Unfortunately, the library will work with what is.
         * But there is an opportunity to recount the balance.
         *
         * Here is an example:
         */
        app(BookkeeperServiceInterface::class)->missing($buyer->wallet);
        self::assertSame(1000, (int) $wallet->getRawOriginal('balance'));

        /**
         * We load the model from the base and our balance is 10.
         */
        $wallet->refresh();
        self::assertSame(10, $wallet->balanceInt);
        self::assertSame(10, (int) $wallet->getRawOriginal('balance'));

        /**
         * Now we fill the cache with relevant data (PS, the data inside the model will be updated).
         */
        $wallet->refreshBalance();
        self::assertSame(1000, $wallet->balanceInt);
        self::assertSame(1000, (int) $wallet->getRawOriginal('balance'));
    }

    public function testFailUpdate(): void
    {
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
        $mockQuery->method('update')->willReturn(0);

        /** @var MockObject|Wallet $mockWallet */
        $mockWallet = $this->createMock(\get_class($wallet));
        $mockWallet->method('newQuery')->willReturn($mockQuery);
        $mockWallet->method('getKey')->willReturn($wallet->getKey());
        $mockWallet->method('fill')->willReturn($mockWallet);
        $mockWallet->method('syncOriginalAttribute')->willReturn($mockWallet);
        $mockWallet->method('__get')->with('uuid')->willReturn($wallet->uuid);

        $bookkeeper = app(BookkeeperServiceInterface::class);
        $regulator = app(RegulatorServiceInterface::class);
        $result = app(CommonServiceLegacy::class)
            ->addBalance($mockWallet, 100)
        ;

        self::assertFalse($result);
        self::assertSame('0', $regulator->amount($wallet));
        self::assertSame('0', $bookkeeper->amount($wallet));
        self::assertSame('0', $wallet->balance);
    }
}
