<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;
use Bavix\Wallet\Test\Models\ItemDiscount;

class DiscountTest extends TestCase
{

    /**
     * @return void
     */
    public function testPay(): void
    {
        /**
         * @var Buyer $buyer
         * @var ItemDiscount $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(ItemDiscount::class)->create();

        self::assertEquals($buyer->balance, 0);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals($transfer->status, Transfer::STATUS_PAID);

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

    /**
     * @return void
     */
    public function testItemTransactions(): void
    {
        /**
         * @var Buyer $buyer
         * @var ItemDiscount $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(ItemDiscount::class)->create();

        self::assertEquals($buyer->balance, 0);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals($transfer->status, Transfer::STATUS_PAID);

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

    /**
     * @return void
     */
    public function testRefund(): void
    {
        /**
         * @var Buyer $buyer
         * @var ItemDiscount $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(ItemDiscount::class)->create([
            'quantity' => 1,
        ]);

        self::assertEquals($buyer->balance, 0);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals($transfer->status, Transfer::STATUS_PAID);

        self::assertEquals(
            $transfer->discount,
            $product->getPersonalDiscount($buyer)
        );

        self::assertTrue($buyer->refund($product));
        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        self::assertEquals($product->balance, 0);

        $transfer->refresh();
        self::assertEquals($transfer->status, Transfer::STATUS_REFUND);

        self::assertFalse($buyer->safeRefund($product));
        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));

        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals($buyer->balance, $product->getPersonalDiscount($buyer));
        self::assertEquals(
            $product->balance,
            $product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer)
        );

        self::assertEquals($transfer->status, Transfer::STATUS_PAID);

        self::assertTrue($buyer->refund($product));
        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        self::assertEquals($product->balance, 0);

        $transfer->refresh();
        self::assertEquals($transfer->status, Transfer::STATUS_REFUND);
    }

    /**
     * @return void
     */
    public function testForceRefund(): void
    {
        /**
         * @var Buyer $buyer
         * @var ItemDiscount $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(ItemDiscount::class)->create([
            'quantity' => 1,
        ]);

        self::assertEquals($buyer->balance, 0);
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
        self::assertEquals($product->balance, 0);

        self::assertFalse($buyer->safeRefund($product));
        self::assertTrue($buyer->forceRefund($product));

        self::assertEquals(
            $product->balance, -($product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer))
        );

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $product->deposit(-$product->balance);
        $buyer->withdraw($buyer->balance);

        self::assertEquals($product->balance, 0);
        self::assertEquals($buyer->balance, 0);
    }

    /**
     * @return void
     */
    public function testOutOfStock(): void
    {
        $this->expectException(ProductEnded::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.product_stock'));

        /**
         * @var Buyer $buyer
         * @var ItemDiscount $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(ItemDiscount::class)->create([
            'quantity' => 1,
        ]);

        $buyer->deposit($product->getAmountProduct($buyer));
        $buyer->pay($product);
        $buyer->pay($product);
    }

    /**
     * @return void
     */
    public function testForcePay(): void
    {
        /**
         * @var Buyer $buyer
         * @var ItemDiscount $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(ItemDiscount::class)->create([
            'quantity' => 1,
        ]);

        self::assertEquals($buyer->balance, 0);
        $buyer->forcePay($product);

        self::assertEquals(
            $buyer->balance, -($product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer))
        );

        $buyer->deposit(-$buyer->balance);
        self::assertEquals($buyer->balance, 0);
    }

    /**
     * @return void
     */
    public function testPayFree(): void
    {
        /**
         * @var Buyer $buyer
         * @var ItemDiscount $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(ItemDiscount::class)->create([
            'quantity' => 1,
        ]);

        self::assertEquals($buyer->balance, 0);

        $transfer = $buyer->payFree($product);
        self::assertEquals($transfer->deposit->type, Transaction::TYPE_DEPOSIT);
        self::assertEquals($transfer->withdraw->type, Transaction::TYPE_WITHDRAW);

        self::assertEquals($buyer->balance, 0);
        self::assertEquals($product->balance, 0);

        $buyer->refund($product);
        self::assertEquals($buyer->balance, 0);
        self::assertEquals($product->balance, 0);
    }

    public function testFreePay(): void
    {
        /**
         * @var Buyer $buyer
         * @var ItemDiscount $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(ItemDiscount::class)->create([
            'quantity' => 1,
        ]);

        $buyer->forceWithdraw(1000);
        self::assertEquals($buyer->balance, -1000);

        $transfer = $buyer->payFree($product);
        self::assertEquals($transfer->deposit->type, Transaction::TYPE_DEPOSIT);
        self::assertEquals($transfer->withdraw->type, Transaction::TYPE_WITHDRAW);

        self::assertEquals($buyer->balance, -1000);
        self::assertEquals($product->balance, 0);

        $buyer->refund($product);
        self::assertEquals($buyer->balance, -1000);
        self::assertEquals($product->balance, 0);
    }

    /**
     * @return void
     */
    public function testPayFreeOutOfStock(): void
    {
        $this->expectException(ProductEnded::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.product_stock'));

        /**
         * @var Buyer $buyer
         * @var ItemDiscount $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(ItemDiscount::class)->create([
            'quantity' => 1,
        ]);

        self::assertNotNull($buyer->payFree($product));
        $buyer->payFree($product);
    }

}
