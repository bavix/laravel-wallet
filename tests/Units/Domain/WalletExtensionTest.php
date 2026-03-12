<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Listeners\WalletStateProjectorListener;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\PackageModels\Transaction;
use Bavix\Wallet\Test\Infra\PackageModels\TransactionMoney;
use Bavix\Wallet\Test\Infra\PackageModels\Wallet;
use Bavix\Wallet\Test\Infra\TestCase;
use Bavix\Wallet\Test\Infra\Transform\TransactionDtoTransformerCustom;
use Illuminate\Database\Events\TransactionCommitting;
use Illuminate\Support\Facades\Event;

/**
 * @internal
 */
final class WalletExtensionTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->app?->bind(TransactionDtoTransformerInterface::class, TransactionDtoTransformerCustom::class);
    }

    public function testCustomAttribute(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $transaction = $buyer->deposit(1000, [
            'bank_method' => 'VietComBank',
        ]);

        self::assertTrue($transaction->getKey() > 0);
        self::assertSame($transaction->amountInt, $buyer->balanceInt);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertSame('VietComBank', $transaction->bank_method);
    }

    public function testTransactionMoneyAttribute(): void
    {
        $this->app?->bind(\Bavix\Wallet\Models\Transaction::class, TransactionMoney::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        /** @var TransactionMoney $transaction */
        $transaction = $buyer->deposit(1000, [
            'currency' => 'EUR',
        ]);

        self::assertTrue($transaction->getKey() > 0);
        self::assertSame($transaction->amountInt, $buyer->balanceInt);
        self::assertInstanceOf(TransactionMoney::class, $transaction);
        self::assertSame('1000', $transaction->currency->amount);
        self::assertSame('EUR', $transaction->currency->currency);
    }

    public function testNoCustomAttribute(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $transaction = $buyer->deposit(1000);

        self::assertSame($transaction->amountInt, $buyer->balanceInt);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertNull($transaction->bank_method);
    }

    public function testWalletStateProjectionViaTransactionCommitting(): void
    {
        Event::listen(TransactionCommitting::class, WalletStateProjectorListener::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $buyer->deposit(150);

        /** @var Wallet $wallet */
        $wallet = Wallet::query()->findOrFail($buyer->wallet->getKey());
        self::assertSame('150', $wallet->final_balance);
        self::assertSame('0', $wallet->frozen_balance);
        self::assertSame(hash('sha256', $wallet->uuid.':150:0'), $wallet->checksum);

        $buyer->withdraw(50);

        /** @var Wallet $wallet */
        $wallet = Wallet::query()->findOrFail($buyer->wallet->getKey());
        self::assertSame('100', $wallet->final_balance);
        self::assertSame('0', $wallet->frozen_balance);
        self::assertSame(hash('sha256', $wallet->uuid.':100:0'), $wallet->checksum);
    }
}
