<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;

class GiftTest extends TestCase
{

    /**
     * @return void
     */
    public function testGift(): void
    {
        /**
         * @var Buyer $first
         * @var Buyer $second
         * @var Item $product
         */
        [$first, $second] = factory(Buyer::class, 2)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        self::assertEquals($first->balance, 0);
        self::assertEquals($second->balance, 0);

        $first->deposit($product->getAmountProduct($first));
        self::assertEquals($first->balance, $product->getAmountProduct($first));

        $transfer = $first->wallet->gift($second, $product);
        self::assertEquals($first->balance, 0);
        self::assertEquals($second->balance, 0);
        self::assertNull($first->paid($product, true));
        self::assertNotNull($second->paid($product, true));
        self::assertNull($second->wallet->paid($product));
        self::assertNotNull($second->wallet->paid($product, true));
        self::assertEquals($transfer->status, Transfer::STATUS_GIFT);
    }

    /**
     * @return void
     */
    public function testRefund(): void
    {
        /**
         * @var Buyer $first
         * @var Buyer $second
         * @var Item $product
         */
        [$first, $second] = factory(Buyer::class, 2)->create();
        $product = factory(Item::class)->create([
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
