<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemMinTaxFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\ItemMinTax;
use Bavix\Wallet\Test\Infra\TestCase;

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
            $fee = (int) $product->getMinimalFee();
        }

        $balance = (int) ($product->getAmountProduct($buyer) + $fee);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($balance);

        self::assertNotSame(0, $buyer->balanceInt);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertSame($withdraw->amountInt, -$balance);
        self::assertSame($deposit->amountInt, (int) $product->getAmountProduct($buyer));
        self::assertNotSame($deposit->amountInt, $withdraw->amountInt);
        self::assertSame((int) $transfer->fee, $fee);

        $buyer->refund($product);
        self::assertSame($buyer->balanceInt, $deposit->amountInt);
        self::assertSame(0, $product->balanceInt);

        $buyer->withdraw($buyer->balance);
        self::assertSame(0, $buyer->balanceInt);
    }
}
