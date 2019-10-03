<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;

class ProductTest extends TestCase
{

    /**
     * @return void
     */
    public function testPay(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertEquals($buyer->balance, 0);
        $buyer->deposit($product->getAmountProduct($buyer));

        $this->assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        $this->assertNotNull($transfer);
        $this->assertEquals($transfer->status, Transfer::STATUS_PAID);

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        $this->assertInstanceOf(Transaction::class, $withdraw);
        $this->assertInstanceOf(Transaction::class, $deposit);

        $this->assertInstanceOf(Buyer::class, $withdraw->payable);
        $this->assertInstanceOf(Item::class, $deposit->payable);

        $this->assertEquals($buyer->getKey(), $withdraw->payable->getKey());
        $this->assertEquals($product->getKey(), $deposit->payable->getKey());

        $this->assertInstanceOf(Buyer::class, $transfer->from->holder);
        $this->assertInstanceOf(Wallet::class, $transfer->from);
        $this->assertInstanceOf(Item::class, $transfer->to);
        $this->assertInstanceOf(Wallet::class, $transfer->to->wallet);

        $this->assertEquals($buyer->wallet->getKey(), $transfer->from->getKey());
        $this->assertEquals($buyer->getKey(), $transfer->from->holder->getKey());
        $this->assertEquals($product->getKey(), $transfer->to->getKey());

        $this->assertEquals($buyer->balance, 0);
        $this->assertNull($buyer->safePay($product));
    }

    /**
     * @return void
     */
    public function testRefund(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertEquals($buyer->balance, 0);
        $buyer->deposit($product->getAmountProduct($buyer));

        $this->assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        $this->assertNotNull($transfer);
        $this->assertEquals($transfer->status, Transfer::STATUS_PAID);

        $this->assertTrue($buyer->refund($product));
        $this->assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $this->assertEquals($product->balance, 0);

        $transfer->refresh();
        $this->assertEquals($transfer->status, Transfer::STATUS_REFUND);

        $this->assertFalse($buyer->safeRefund($product));
        $this->assertEquals($buyer->balance, $product->getAmountProduct($buyer));

        $transfer = $buyer->pay($product);
        $this->assertNotNull($transfer);
        $this->assertEquals($buyer->balance, 0);
        $this->assertEquals($product->balance, $product->getAmountProduct($buyer));
        $this->assertEquals($transfer->status, Transfer::STATUS_PAID);

        $this->assertTrue($buyer->refund($product));
        $this->assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $this->assertEquals($product->balance, 0);

        $transfer->refresh();
        $this->assertEquals($transfer->status, Transfer::STATUS_REFUND);
    }

    /**
     * @return void
     */
    public function testForceRefund(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertEquals($buyer->balance, 0);
        $buyer->deposit($product->getAmountProduct($buyer));

        $this->assertEquals($buyer->balance, $product->getAmountProduct($buyer));

        $buyer->pay($product);
        $this->assertEquals($buyer->balance, 0);
        $this->assertEquals($product->balance, $product->getAmountProduct($buyer));

        $product->withdraw($product->balance);
        $this->assertEquals($product->balance, 0);

        $this->assertFalse($buyer->safeRefund($product));
        $this->assertTrue($buyer->forceRefund($product));

        $this->assertEquals($product->balance, -$product->getAmountProduct($buyer));
        $this->assertEquals($buyer->balance, $product->getAmountProduct($buyer));
        $product->deposit(-$product->balance);
        $buyer->withdraw($buyer->balance);

        $this->assertEquals($product->balance, 0);
        $this->assertEquals($buyer->balance, 0);
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
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
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
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertEquals($buyer->balance, 0);
        $buyer->forcePay($product);

        $this->assertEquals($buyer->balance, -$product->getAmountProduct($buyer));

        $buyer->deposit(-$buyer->balance);
        $this->assertEquals($buyer->balance, 0);
    }

    /**
     * @return void
     */
    public function testPayFree(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertEquals($buyer->balance, 0);

        $transfer = $buyer->payFree($product);
        $this->assertEquals($transfer->deposit->type, Transaction::TYPE_DEPOSIT);
        $this->assertEquals($transfer->withdraw->type, Transaction::TYPE_WITHDRAW);

        $this->assertEquals($buyer->balance, 0);
        $this->assertEquals($product->balance, 0);

        $buyer->refund($product);
        $this->assertEquals($buyer->balance, 0);
        $this->assertEquals($product->balance, 0);
    }

    public function testFreePay(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $buyer->forceWithdraw(1000);
        $this->assertEquals($buyer->balance, -1000);

        $transfer = $buyer->payFree($product);
        $this->assertEquals($transfer->deposit->type, Transaction::TYPE_DEPOSIT);
        $this->assertEquals($transfer->withdraw->type, Transaction::TYPE_WITHDRAW);

        $this->assertEquals($buyer->balance, -1000);
        $this->assertEquals($product->balance, 0);

        $buyer->refund($product);
        $this->assertEquals($buyer->balance, -1000);
        $this->assertEquals($product->balance, 0);
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
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertNotNull($buyer->payFree($product));
        $buyer->payFree($product);
    }

}
