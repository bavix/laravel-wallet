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

        $this->assertEquals($first->balance, 0);
        $this->assertEquals($second->balance, 0);

        $first->deposit($product->getAmountProduct());
        $this->assertEquals($first->balance, $product->getAmountProduct());

        $transfer = $first->wallet->gift($second, $product);
        $this->assertEquals($first->balance, 0);
        $this->assertEquals($second->balance, 0);
        $this->assertNull($first->paid($product, true));
        $this->assertNotNull($second->paid($product, true));
        $this->assertEquals($transfer->status, Transfer::STATUS_GIFT);
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

        $this->assertEquals($first->balance, 0);
        $this->assertEquals($second->balance, 0);

        $first->deposit($product->getAmountProduct());
        $this->assertEquals($first->balance, $product->getAmountProduct());

        $transfer = $first->wallet->gift($second, $product);
        $this->assertEquals($first->balance, 0);
        $this->assertEquals($second->balance, 0);
        $this->assertEquals($transfer->status, Transfer::STATUS_GIFT);

        $this->assertFalse($second->wallet->safeRefund($product));
        $this->assertTrue($second->wallet->refundGift($product));

        $this->assertEquals($first->balance, $product->getAmountProduct());
        $this->assertEquals($second->balance, 0);

        $this->assertNull($second->wallet->safeGift($first, $product));

        $transfer = $second->wallet->forceGift($first, $product);
        $this->assertNotNull($transfer);
        $this->assertEquals($transfer->status, Transfer::STATUS_GIFT);

        $this->assertEquals($second->balance, -$product->getAmountProduct());

        $second->deposit(-$second->balance);
        $this->assertEquals($second->balance, 0);

        $first->withdraw($product->getAmountProduct());
        $this->assertEquals($first->balance, 0);

        $product->withdraw($product->balance);
        $this->assertEquals($product->balance, 0);

        $this->assertFalse($first->safeRefundGift($product));
        $this->assertTrue($first->forceRefundGift($product));
        $this->assertEquals($product->balance, -$product->getAmountProduct());

        $first->withdraw($first->balance);
        $this->assertEquals($first->balance, 0);
    }

}
