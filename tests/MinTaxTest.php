<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Factories\ItemMinTaxFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\ItemMinTax;

/**
 * @internal
 */
class MinTaxTest extends TestCase
{
    public function testPay(): void
    {
        /**
         * @var Buyer      $buyer
         * @var ItemMinTax $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemMinTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        $fee = (int) ($product->getAmountProduct($buyer) * $product->getFeePercent() / 100);
        if ($fee < $product->getMinimalFee()) {
            $fee = $product->getMinimalFee();
        }

        $balance = $product->getAmountProduct($buyer) + $fee;

        self::assertEquals($buyer->balance, 0);
        $buyer->deposit($balance);

        self::assertNotEquals($buyer->balance, 0);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertEquals($withdraw->amount, -$balance);
        self::assertEquals($deposit->amount, $product->getAmountProduct($buyer));
        self::assertNotEquals($deposit->amount, $withdraw->amount);
        self::assertEquals($transfer->fee, $fee);

        $buyer->refund($product);
        self::assertEquals($buyer->balance, $deposit->amount);
        self::assertEquals($product->balance, 0);

        $buyer->withdraw($buyer->balance);
        self::assertEquals($buyer->balance, 0);
    }
}
