<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\ItemTax;

class TaxTest extends TestCase
{

    /**
     * @return void
     */
    public function testPay(): void
    {
        /**
         * @var Buyer $buyer
         * @var ItemTax $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(ItemTax::class)->create([
            'quantity' => 1,
        ]);

        $fee = (int)($product->getAmountProduct($buyer) * $product->getFeePercent() / 100);
        $balance = $product->getAmountProduct($buyer) + $fee;

        $this->assertEquals($buyer->balance, 0);
        $buyer->deposit($balance);

        $this->assertNotEquals($buyer->balance, 0);
        $transfer = $buyer->pay($product);
        $this->assertNotNull($transfer);

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        $this->assertEquals($withdraw->amount, -$balance);
        $this->assertEquals($deposit->amount, $product->getAmountProduct($buyer));
        $this->assertNotEquals($deposit->amount, $withdraw->amount);
        $this->assertEquals($transfer->fee, $fee);

        $buyer->refund($product);
        $this->assertEquals($buyer->balance, $deposit->amount);
        $this->assertEquals($product->balance, 0);

        $buyer->withdraw($buyer->balance);
        $this->assertEquals($buyer->balance, 0);
    }

    /**
     * @return void
     */
    public function testGift(): void
    {
        /**
         * @var Buyer $santa
         * @var Buyer $child
         * @var ItemTax $product
         */
        [$santa, $child] = factory(Buyer::class, 2)->create();
        $product = factory(ItemTax::class)->create([
            'quantity' => 1,
        ]);

        $fee = (int)($product->getAmountProduct($santa) * $product->getFeePercent() / 100);
        $balance = $product->getAmountProduct($santa) + $fee;

        $this->assertEquals($santa->balance, 0);
        $this->assertEquals($child->balance, 0);
        $santa->deposit($balance);

        $this->assertNotEquals($santa->balance, 0);
        $this->assertEquals($child->balance, 0);
        $transfer = $santa->wallet->gift($child, $product);
        $this->assertNotNull($transfer);

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        $this->assertEquals($withdraw->amount, -$balance);
        $this->assertEquals($deposit->amount, $product->getAmountProduct($santa));
        $this->assertNotEquals($deposit->amount, $withdraw->amount);
        $this->assertEquals($transfer->fee, $fee);

        $this->assertFalse($santa->safeRefundGift($product));
        $this->assertTrue($child->refundGift($product));
        $this->assertEquals($santa->balance, $deposit->amount);
        $this->assertEquals($child->balance, 0);
        $this->assertEquals($product->balance, 0);

        $santa->withdraw($santa->balance);
        $this->assertEquals($santa->balance, 0);
    }

    /**
     * @return void
     */
    public function testGiftFail(): void
    {
        $this->expectException(InsufficientFunds::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.insufficient_funds'));

        /**
         * @var Buyer $santa
         * @var Buyer $child
         * @var ItemTax $product
         */
        [$santa, $child] = factory(Buyer::class, 2)->create();
        $product = factory(ItemTax::class)->create([
            'price' => 200,
            'quantity' => 1,
        ]);

        $this->assertEquals($santa->balance, 0);
        $this->assertEquals($child->balance, 0);
        $santa->deposit($product->getAmountProduct($santa));

        $this->assertNotEquals($santa->balance, 0);
        $this->assertEquals($child->balance, 0);
        $santa->wallet->gift($child, $product);

        $this->assertEquals($santa->balance, 0);
    }

}
