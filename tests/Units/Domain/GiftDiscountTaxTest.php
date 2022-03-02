<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\TaxServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemDiscountTaxFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\ItemDiscountTax;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class GiftDiscountTaxTest extends TestCase
{
    public function testGift(): void
    {
        /**
         * @var Buyer           $first
         * @var Buyer           $second
         * @var ItemDiscountTax $product
         */
        [$first, $second] = BuyerFactory::times(2)->create();
        $product = ItemDiscountTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $first->balanceInt);
        self::assertSame(0, $second->balanceInt);

        $fee = app(TaxServiceInterface::class)->getFee(
            $product,
            $product->getAmountProduct($first) - $product->getPersonalDiscount($first)
        );

        $first->deposit($product->getAmountProduct($first) + $fee);
        self::assertSame($first->balanceInt, (int) ($product->getAmountProduct($first) + $fee));

        $transfer = $first->wallet->gift($second, $product);
        self::assertSame($first->balanceInt, (int) $product->getPersonalDiscount($first));
        self::assertSame($second->balanceInt, 0);
        self::assertNull($first->paid($product, true));
        self::assertNotNull($second->paid($product, true));
        self::assertNull($second->wallet->paid($product));
        self::assertNotNull($second->wallet->paid($product, true));
        self::assertSame($transfer->status, Transfer::STATUS_GIFT);
    }

    public function testRefund(): void
    {
        /**
         * @var Buyer           $first
         * @var Buyer           $second
         * @var ItemDiscountTax $product
         */
        [$first, $second] = BuyerFactory::times(2)->create();
        $product = ItemDiscountTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame($first->balanceInt, 0);
        self::assertSame($second->balanceInt, 0);

        $fee = app(TaxServiceInterface::class)->getFee(
            $product,
            $product->getAmountProduct($first) - $product->getPersonalDiscount($first)
        );

        $first->deposit($product->getAmountProduct($first) + $fee);
        self::assertSame($first->balanceInt, (int) $product->getAmountProduct($first) + $fee);

        $transfer = $first->wallet->gift($second, $product);
        self::assertSame($first->balance, (string) $product->getPersonalDiscount($first));
        self::assertSame($second->balanceInt, 0);
        self::assertSame($transfer->status, Transfer::STATUS_GIFT);

        $first->withdraw($product->getPersonalDiscount($first));
        self::assertSame($first->balanceInt, 0);

        self::assertFalse($second->wallet->safeRefund($product));
        self::assertTrue($second->wallet->refundGift($product));

        self::assertSame(
            $first->balanceInt,
            (int) ($product->getAmountProduct($first) - $product->getPersonalDiscount($first))
        );

        $first->withdraw($first->balance);
        self::assertSame($first->balanceInt, 0);
        self::assertSame($second->balanceInt, 0);

        self::assertNull($second->wallet->safeGift($first, $product));

        $secondFee = app(TaxServiceInterface::class)->getFee(
            $product,
            (int) ($product->getAmountProduct($second) - $product->getPersonalDiscount($second))
        );

        $transfer = $second->wallet->forceGift($first, $product);
        self::assertNotNull($transfer);
        self::assertSame($transfer->status, Transfer::STATUS_GIFT);

        self::assertSame(
            $second->balanceInt,
            (int) -(($product->getAmountProduct($second) + $secondFee) - $product->getPersonalDiscount($second))
        );

        $second->deposit(-$second->balance);
        self::assertSame($second->balanceInt, 0);
        self::assertSame($first->balanceInt, 0);

        $product->withdraw($product->balance);
        self::assertSame($product->balanceInt, 0);

        self::assertFalse($first->safeRefundGift($product));
        self::assertTrue($first->forceRefundGift($product));

        self::assertSame($second->balanceInt, -$product->balanceInt);

        self::assertSame(
            $product->balanceInt,
            (int) -($product->getAmountProduct($second) - $product->getPersonalDiscount($second))
        );

        self::assertSame(
            $second->balanceInt,
            $product->getAmountProduct($second) - $product->getPersonalDiscount($second)
        );

        $second->withdraw($second->balance);
        self::assertSame($second->balanceInt, 0);
    }
}
