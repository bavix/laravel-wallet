<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Factories\ItemFactory;
use Bavix\Wallet\Test\Factories\ItemWalletFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;
use Bavix\Wallet\Test\Models\ItemWallet;

/**
 * @internal
 */
class ProductTest extends TestCase
{
    public function testPay(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals($buyer->balance, 0);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals($transfer->status, Transfer::STATUS_PAID);

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertInstanceOf(Transaction::class, $withdraw);
        self::assertInstanceOf(Transaction::class, $deposit);

        self::assertInstanceOf(Buyer::class, $withdraw->payable);
        self::assertInstanceOf(Item::class, $deposit->payable);

        self::assertEquals($buyer->getKey(), $withdraw->payable->getKey());
        self::assertEquals($product->getKey(), $deposit->payable->getKey());

        self::assertInstanceOf(Buyer::class, $transfer->from->holder);
        self::assertInstanceOf(Wallet::class, $transfer->from);
        self::assertInstanceOf(Item::class, $transfer->to);
        self::assertInstanceOf(Wallet::class, $transfer->to->wallet);

        self::assertEquals($buyer->wallet->getKey(), $transfer->from->getKey());
        self::assertEquals($buyer->getKey(), $transfer->from->holder->getKey());
        self::assertEquals($product->getKey(), $transfer->to->getKey());

        self::assertEquals(0, $buyer->balance);
        self::assertNull($buyer->safePay($product));
    }

    public function testRefund(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals(0, $buyer->balance);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertEquals($product->getAmountProduct($buyer), $buyer->balance);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals(Transfer::STATUS_PAID, $transfer->status);

        self::assertTrue($buyer->refund($product));
        self::assertEquals($product->getAmountProduct($buyer), $buyer->balance);
        self::assertEquals(0, $product->balance);

        $transfer->refresh();
        self::assertEquals(Transfer::STATUS_REFUND, $transfer->status);

        self::assertFalse($buyer->safeRefund($product));
        self::assertEquals($product->getAmountProduct($buyer), $buyer->balance);

        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals(0, $buyer->balance);
        self::assertEquals($product->getAmountProduct($buyer), $product->balance);
        self::assertEquals(Transfer::STATUS_PAID, $transfer->status);

        self::assertTrue($buyer->refund($product));
        self::assertEquals($product->getAmountProduct($buyer), $buyer->balance);
        self::assertEquals(0, $product->balance);

        $transfer->refresh();
        self::assertEquals(Transfer::STATUS_REFUND, $transfer->status);
    }

    public function testForceRefund(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals(0, $buyer->balance);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertEquals($product->getAmountProduct($buyer), $buyer->balance);

        $buyer->pay($product);
        self::assertEquals(0, $buyer->balance);
        self::assertEquals($product->getAmountProduct($buyer), $product->balance);

        $product->withdraw($product->balance);
        self::assertEquals(0, $product->balance);

        self::assertFalse($buyer->safeRefund($product));
        self::assertTrue($buyer->forceRefund($product));

        self::assertEquals(-$product->getAmountProduct($buyer), $product->balance);
        self::assertEquals($product->getAmountProduct($buyer), $buyer->balance);
        $product->deposit(-$product->balance);
        $buyer->withdraw($buyer->balance);

        self::assertEquals(0, $product->balance);
        self::assertEquals(0, $buyer->balance);
    }

    public function testOutOfStock(): void
    {
        $this->expectException(ProductEnded::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.product_stock'));

        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        $buyer->deposit($product->getAmountProduct($buyer));
        $buyer->pay($product);
        $buyer->pay($product);
    }

    public function testForcePay(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals(0, $buyer->balance);
        $buyer->forcePay($product);

        self::assertEquals(-$product->getAmountProduct($buyer), $buyer->balance);

        $buyer->deposit(-$buyer->balance);
        self::assertEquals(0, $buyer->balance);
    }

    public function testPayFree(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals(0, $buyer->balance);

        $transfer = $buyer->payFree($product);
        self::assertEquals(Transaction::TYPE_DEPOSIT, $transfer->deposit->type);
        self::assertEquals(Transaction::TYPE_WITHDRAW, $transfer->withdraw->type);

        self::assertEquals(0, $buyer->balance);
        self::assertEquals(0, $product->balance);

        $buyer->refund($product);
        self::assertEquals(0, $buyer->balance);
        self::assertEquals(0, $product->balance);
    }

    public function testFreePay(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        $buyer->forceWithdraw(1000);
        self::assertEquals(-1000, $buyer->balance);

        $transfer = $buyer->payFree($product);
        self::assertEquals(Transaction::TYPE_DEPOSIT, $transfer->deposit->type);
        self::assertEquals(Transaction::TYPE_WITHDRAW, $transfer->withdraw->type);

        self::assertEquals(-1000, $buyer->balance);
        self::assertEquals(0, $product->balance);

        $buyer->refund($product);
        self::assertEquals(-1000, $buyer->balance);
        self::assertEquals(0, $product->balance);
    }

    public function testPayFreeOutOfStock(): void
    {
        $this->expectException(ProductEnded::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.product_stock'));

        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertNotNull($buyer->payFree($product));
        $buyer->payFree($product);
    }

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/237
     *
     * @throws
     */
    public function testProductMultiWallet(): void
    {
        /**
         * @var Buyer      $buyer
         * @var ItemWallet $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemWalletFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals(0, $buyer->balance);
        $buyer->deposit($product->getAmountProduct($buyer));
        self::assertEquals($product->getAmountProduct($buyer), $buyer->balance);

        $product->createWallet(['name' => 'testing']);
        app(DbService::class)->transaction(function () use ($product, $buyer) {
            $transfer = $buyer->pay($product);
            $product->transfer($product->getWallet('testing'), $transfer->deposit->amount, $transfer->toArray());
        });

        self::assertEquals(0, $product->balance);
        self::assertEquals(0, $buyer->balance);
        self::assertEquals($product->getAmountProduct($buyer), $product->getWallet('testing')->balance);
    }
}
