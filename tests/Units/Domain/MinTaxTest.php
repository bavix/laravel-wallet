<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemMaxTaxFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemMinTaxFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\ItemMaxTax;
use Bavix\Wallet\Test\Infra\Models\ItemMinTax;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class MinTaxTest extends TestCase
{
    public function testPayMinimalTax(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var ItemMinTax $product */
        $product = ItemMinTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        $fee = (int) ($product->getAmountProduct($buyer) * $product->getFeePercent() / 100);
        if ($fee < $product->getMinimalFee()) {
            $fee = $product->getMinimalFee();
        }

        $balance = $product->getAmountProduct($buyer) + $fee;

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($balance);

        self::assertNotSame(0, $buyer->balanceInt);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);

        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertSame($withdraw->amountInt, -$balance);
        self::assertSame($deposit->amountInt, $product->getAmountProduct($buyer));
        self::assertNotSame($deposit->amountInt, $withdraw->amountInt);
        self::assertSame((int) $transfer->fee, $fee);

        $buyer->refund($product);
        self::assertSame($buyer->balanceInt, $deposit->amountInt);
        self::assertSame(0, $product->balanceInt);

        $buyer->withdraw($buyer->balance);
        self::assertSame(0, $buyer->balanceInt);
    }

    public function testPayMaximalTax(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var ItemMaxTax $product */
        $product = ItemMaxTaxFactory::new()->create([
            'quantity' => 1,
            'price' => 12000,
        ]);

        $fee = (int) ($product->getAmountProduct($buyer) * $product->getFeePercent() / 100);
        if ($fee > $product->getMaximalFee()) {
            $fee = $product->getMaximalFee();
        }

        $balance = $product->getAmountProduct($buyer) + $fee;

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($balance);

        self::assertNotSame(0, $buyer->balanceInt);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);

        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertSame($withdraw->amountInt, -$balance);
        self::assertSame($deposit->amountInt, $product->getAmountProduct($buyer));
        self::assertNotSame($deposit->amountInt, $withdraw->amountInt);
        self::assertSame((int) $transfer->fee, $fee);

        $buyer->refund($product);
        self::assertSame($buyer->balanceInt, $deposit->amountInt);
        self::assertSame(0, $product->balanceInt);

        $buyer->withdraw($buyer->balance);
        self::assertSame(0, $buyer->balanceInt);
    }
}
