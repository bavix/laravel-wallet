<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;
use Bavix\Wallet\Test\Common\Models\Transaction;
use Bavix\Wallet\Test\Common\Models\TransactionMoney;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Models\Buyer;

/**
 * @internal
 * @coversNothing
 */
class WalletExtensionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->bind(TransactionDtoTransformer::class, Objects\TransactionDtoTransformer::class);
    }

    public function testCustomAttribute(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $transaction = $buyer->deposit(1000, ['bank_method' => 'VietComBank']);

        self::assertEquals($transaction->amount, $buyer->balance);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertEquals('VietComBank', $transaction->bank_method);
    }

    public function testTransactionMoneyAttribute(): void
    {
        $this->app->bind(\Bavix\Wallet\Models\Transaction::class, TransactionMoney::class);

        /**
         * @var Buyer            $buyer
         * @var TransactionMoney $transaction
         */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $transaction = $buyer->deposit(1000, ['currency' => 'EUR']);

        self::assertEquals($transaction->amount, $buyer->balance);
        self::assertInstanceOf(TransactionMoney::class, $transaction);
        self::assertEquals(1000, $transaction->currency->getAmount());
        self::assertEquals('EUR', $transaction->currency->getCurrency()->getCode());
    }

    public function testNoCustomAttribute(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $transaction = $buyer->deposit(1000);

        self::assertEquals($transaction->amount, $buyer->balance);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertNull($transaction->bank_method);
    }
}
