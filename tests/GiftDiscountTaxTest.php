<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Factories\ItemDiscountTaxFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\ItemDiscountTax;

/**
 * @internal
 */
class GiftDiscountTaxTest extends TestCase
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

        self::assertEquals($first->balance, 0);
        self::assertEquals($second->balance, 0);

        $fee = app(WalletService::class)->fee(
            $product,
            $product->getAmountProduct($first) - $product->getPersonalDiscount($first)
        );

        $first->deposit($product->getAmountProduct($first) + $fee);
        self::assertEquals(
            $first->balance,
            $product->getAmountProduct($first) + $fee
        );

        $transfer = $first->wallet->gift($second, $product);
        self::assertEquals($first->balance, $product->getPersonalDiscount($first));
        self::assertEquals($second->balance, 0);
        self::assertNull($first->paid($product, true));
        self::assertNotNull($second->paid($product, true));
        self::assertNull($second->wallet->paid($product));
        self::assertNotNull($second->wallet->paid($product, true));
        self::assertEquals($transfer->status, Transfer::STATUS_GIFT);
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

        self::assertEquals($first->balance, 0);
        self::assertEquals($second->balance, 0);

        $fee = app(WalletService::class)->fee(
            $product,
            $product->getAmountProduct($first) - $product->getPersonalDiscount($first)
        );

        $first->deposit($product->getAmountProduct($first) + $fee);
        self::assertEquals($first->balance, $product->getAmountProduct($first) + $fee);

        $transfer = $first->wallet->gift($second, $product);
        self::assertEquals($first->balance, $product->getPersonalDiscount($first));
        self::assertEquals($second->balance, 0);
        self::assertEquals($transfer->status, Transfer::STATUS_GIFT);

        $first->withdraw($product->getPersonalDiscount($first));
        self::assertEquals($first->balance, 0);

        self::assertFalse($second->wallet->safeRefund($product));
        self::assertTrue($second->wallet->refundGift($product));

        self::assertEquals(
            $first->balance,
            $product->getAmountProduct($first) - $product->getPersonalDiscount($first)
        );

        $first->withdraw($first->balance);
        self::assertEquals($first->balance, 0);
        self::assertEquals($second->balance, 0);

        self::assertNull($second->wallet->safeGift($first, $product));

        $secondFee = app(WalletService::class)->fee(
            $product,
            $product->getAmountProduct($second) - $product->getPersonalDiscount($second)
        );

        $transfer = $second->wallet->forceGift($first, $product);
        self::assertNotNull($transfer);
        self::assertEquals($transfer->status, Transfer::STATUS_GIFT);

        self::assertEquals(
            $second->balance,
            -(($product->getAmountProduct($second) + $secondFee) - $product->getPersonalDiscount($second))
        );

        $second->deposit(-$second->balance);
        self::assertEquals($second->balance, 0);
        self::assertEquals($first->balance, 0);

        $product->withdraw($product->balance);
        self::assertEquals($product->balance, 0);

        self::assertFalse($first->safeRefundGift($product));
        self::assertTrue($first->forceRefundGift($product));

        self::assertEquals($second->balance, -$product->balance);

        self::assertEquals(
            $product->balance,
            -($product->getAmountProduct($second) - $product->getPersonalDiscount($second))
        );

        self::assertEquals(
            $second->balance,
            $product->getAmountProduct($second) - $product->getPersonalDiscount($second)
        );

        $second->withdraw($second->balance);
        self::assertEquals($second->balance, 0);
    }
}
