<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemTaxFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\ItemTax;
use Bavix\Wallet\Test\Infra\TestCase;

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
        self::assertSame($product->balanceInt, 0);

        $buyer->withdraw($buyer->balance);
        self::assertSame($buyer->balanceInt, 0);
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
        $balance = (int) ($product->getAmountProduct($santa) + $fee);

        self::assertSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        $santa->deposit($balance);

        self::assertNotSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        $transfer = $santa->wallet->gift($child, $product);
        self::assertNotNull($transfer);

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertSame($withdraw->amountInt, -$balance);
        self::assertSame($deposit->amountInt, (int) $product->getAmountProduct($santa));
        self::assertNotSame($deposit->amountInt, $withdraw->amountInt);
        self::assertSame($fee, (int) $transfer->fee);

        self::assertFalse($santa->safeRefundGift($product));
        self::assertTrue($child->refundGift($product));
        self::assertSame($santa->balanceInt, $deposit->amountInt);
        self::assertSame($child->balanceInt, 0);
        self::assertSame($product->balanceInt, 0);

        $santa->withdraw($santa->balance);
        self::assertSame($santa->balanceInt, 0);
    }

    public function testGiftFail(): void
    {
        $this->expectException(InsufficientFunds::class);
        $this->expectExceptionCode(ExceptionInterface::INSUFFICIENT_FUNDS);
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

        self::assertSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        $santa->deposit($product->getAmountProduct($santa));

        self::assertNotSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        $santa->wallet->gift($child, $product);

        self::assertSame($santa->balanceInt, 0);
    }
}
