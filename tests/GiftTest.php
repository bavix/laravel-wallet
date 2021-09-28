<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Factories\ItemFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;

/**
 * @internal
 */
class GiftTest extends TestCase
{
    public function testGift(): void
    {
        /**
         * @var Buyer $first
         * @var Buyer $second
         * @var Item  $product
         */
        [$first, $second] = BuyerFactory::times(2)->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals(0, $first->balance);
        self::assertEquals(0, $second->balance);

        $first->deposit($product->getAmountProduct($first));
        self::assertEquals($first->balance, $product->getAmountProduct($first));

        $transfer = $first->wallet->gift($second, $product);
        self::assertEquals(0, $first->balance);
        self::assertEquals(0, $second->balance);
        self::assertNull($first->paid($product, true));
        self::assertNotNull($second->paid($product, true));
        self::assertNull($second->wallet->paid($product));
        self::assertNotNull($second->wallet->paid($product, true));
        self::assertEquals(Transfer::STATUS_GIFT, $transfer->status);
    }

    public function testRefund(): void
    {
        /**
         * @var Buyer $first
         * @var Buyer $second
         * @var Item  $product
         */
        [$first, $second] = BuyerFactory::times(2)->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals($first->balance, 0);
        self::assertEquals($second->balance, 0);

        $first->deposit($product->getAmountProduct($first));
        self::assertEquals($first->balance, $product->getAmountProduct($first));

        $transfer = $first->wallet->gift($second, $product);
        self::assertEquals($first->balance, 0);
        self::assertEquals($second->balance, 0);
        self::assertEquals($transfer->status, Transfer::STATUS_GIFT);

        self::assertFalse($second->wallet->safeRefund($product));
        self::assertTrue($second->wallet->refundGift($product));

        self::assertEquals($first->balance, $product->getAmountProduct($first));
        self::assertEquals($second->balance, 0);

        self::assertNull($second->wallet->safeGift($first, $product));

        $transfer = $second->wallet->forceGift($first, $product);
        self::assertNotNull($transfer);
        self::assertEquals($transfer->status, Transfer::STATUS_GIFT);

        self::assertEquals($second->balance, -$product->getAmountProduct($second));

        $second->deposit(-$second->balance);
        self::assertEquals($second->balance, 0);

        $first->withdraw($product->getAmountProduct($first));
        self::assertEquals($first->balance, 0);

        $product->withdraw($product->balance);
        self::assertEquals($product->balance, 0);

        self::assertFalse($first->safeRefundGift($product));
        self::assertTrue($first->forceRefundGift($product));
        self::assertEquals($product->balance, -$product->getAmountProduct($second));

        self::assertEquals($second->balance, $product->getAmountProduct($second));
        $second->withdraw($second->balance);
        self::assertEquals($second->balance, 0);
    }
}
