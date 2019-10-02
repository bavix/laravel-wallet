<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\ItemMinTax;

class MinTaxTest extends TestCase
{

    /**
     * @return void
     */
    public function testPay(): void
    {
        /**
         * @var Buyer $buyer
         * @var ItemMinTax $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(ItemMinTax::class)->create([
            'quantity' => 1,
        ]);

        $fee = (int)($product->getAmountProduct($buyer) * $product->getFeePercent() / 100);
        if ($fee < $product->getMinimalFee()) {
            $fee = $product->getMinimalFee();
        }

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

}
