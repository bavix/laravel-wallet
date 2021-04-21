<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Factories\ItemDiscountFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;
use Bavix\Wallet\Test\Models\ItemDiscount;

/**
 * @internal
 */
class DiscountTest extends TestCase
{
    public function testPay(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create();

        self::assertEquals(0, $buyer->balance);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals(Transfer::STATUS_PAID, $transfer->status);

        self::assertEquals(
            $buyer->balance,
            $product->getPersonalDiscount($buyer)
        );

        self::assertEquals(
            $transfer->discount,
            $product->getPersonalDiscount($buyer)
        );

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
    }

    public function testItemTransactions(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create();

        self::assertEquals(0, $buyer->balance);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals(Transfer::STATUS_PAID, $transfer->status);

        self::assertEquals(
            $buyer->balance,
            $product->getPersonalDiscount($buyer)
        );

        self::assertEquals(
            $transfer->discount,
            $product->getPersonalDiscount($buyer)
        );

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertInstanceOf(Transaction::class, $withdraw);
        self::assertInstanceOf(Transaction::class, $deposit);

        self::assertTrue($withdraw->is(
            $buyer->transactions()
                ->where('type', Transaction::TYPE_WITHDRAW)
                ->latest()
                ->first()
        ));

        self::assertTrue($deposit->is($product->transactions()->latest()->first()));
    }

    public function testRefund(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals(0, $buyer->balance);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals(Transfer::STATUS_PAID, $transfer->status);

        self::assertEquals(
            $transfer->discount,
            $product->getPersonalDiscount($buyer)
        );

        self::assertTrue($buyer->refund($product));
        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        self::assertEquals(0, $product->balance);

        $transfer->refresh();
        self::assertEquals(Transfer::STATUS_REFUND, $transfer->status);

        self::assertFalse($buyer->safeRefund($product));
        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));

        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals($buyer->balance, $product->getPersonalDiscount($buyer));
        self::assertEquals(
            $product->balance,
            $product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer)
        );

        self::assertEquals(Transfer::STATUS_PAID, $transfer->status);

        self::assertTrue($buyer->refund($product));
        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        self::assertEquals(0, $product->balance);

        $transfer->refresh();
        self::assertEquals(Transfer::STATUS_REFUND, $transfer->status);
    }

    public function testForceRefund(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals(0, $buyer->balance);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));

        $transfer = $buyer->pay($product);
        self::assertEquals($buyer->balance, $product->getPersonalDiscount($buyer));

        self::assertEquals(
            $product->balance,
            $product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer)
        );

        self::assertEquals(
            $transfer->discount,
            $product->getPersonalDiscount($buyer)
        );

        $product->withdraw($product->balance);
        self::assertEquals(0, $product->balance);

        self::assertFalse($buyer->safeRefund($product));
        self::assertTrue($buyer->forceRefund($product));

        self::assertEquals(
            $product->balance,
            -($product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer))
        );

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
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
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        $buyer->deposit($product->getAmountProduct($buyer));
        $buyer->pay($product);
        $buyer->pay($product);
    }

    public function testForcePay(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals(0, $buyer->balance);
        $buyer->forcePay($product);

        self::assertEquals(
            $buyer->balance,
            -($product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer))
        );

        $buyer->deposit(-$buyer->balance);
        self::assertEquals(0, $buyer->balance);
    }

    public function testPayFree(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
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
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
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
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertNotNull($buyer->payFree($product));
        $buyer->payFree($product);
    }
}
