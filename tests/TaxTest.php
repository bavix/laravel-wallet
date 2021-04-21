<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Factories\ItemTaxFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\ItemTax;

/**
 * @internal
 */
class TaxTest extends TestCase
{
    public function testPay(): void
    {
        /**
         * @var Buyer   $buyer
         * @var ItemTax $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        $fee = (int) ($product->getAmountProduct($buyer) * $product->getFeePercent() / 100);
        $balance = $product->getAmountProduct($buyer) + $fee;

        self::assertEquals(0, $buyer->balance);
        $buyer->deposit($balance);

        self::assertNotEquals(0, $buyer->balance);
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
        self::assertEquals(0, $product->balance);

        $buyer->withdraw($buyer->balance);
        self::assertEquals(0, $buyer->balance);
    }

    public function testGift(): void
    {
        /**
         * @var Buyer   $santa
         * @var Buyer   $child
         * @var ItemTax $product
         */
        [$santa, $child] = BuyerFactory::times(2)->create();
        $product = ItemTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        $fee = (int) ($product->getAmountProduct($santa) * $product->getFeePercent() / 100);
        $balance = $product->getAmountProduct($santa) + $fee;

        self::assertEquals(0, $santa->balance);
        self::assertEquals(0, $child->balance);
        $santa->deposit($balance);

        self::assertNotEquals(0, $santa->balance);
        self::assertEquals(0, $child->balance);
        $transfer = $santa->wallet->gift($child, $product);
        self::assertNotNull($transfer);

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertEquals($withdraw->amount, -$balance);
        self::assertEquals($deposit->amount, $product->getAmountProduct($santa));
        self::assertNotEquals($deposit->amount, $withdraw->amount);
        self::assertEquals($transfer->fee, $fee);

        self::assertFalse($santa->safeRefundGift($product));
        self::assertTrue($child->refundGift($product));
        self::assertEquals($santa->balance, $deposit->amount);
        self::assertEquals(0, $child->balance);
        self::assertEquals(0, $product->balance);

        $santa->withdraw($santa->balance);
        self::assertEquals(0, $santa->balance);
    }

    public function testGiftFail(): void
    {
        $this->expectException(InsufficientFunds::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.insufficient_funds'));

        /**
         * @var Buyer   $santa
         * @var Buyer   $child
         * @var ItemTax $product
         */
        [$santa, $child] = BuyerFactory::times(2)->create();
        $product = ItemTaxFactory::new()->create([
            'price' => 200,
            'quantity' => 1,
        ]);

        self::assertEquals(0, $santa->balance);
        self::assertEquals(0, $child->balance);
        $santa->deposit($product->getAmountProduct($santa));

        self::assertNotEquals(0, $santa->balance);
        self::assertEquals(0, $child->balance);
        $santa->wallet->gift($child, $product);

        self::assertEquals(0, $santa->balance);
    }
}
