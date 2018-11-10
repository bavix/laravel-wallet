<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;

class ProductTest extends TestCase
{

    /**
     * @return void
     */
    public function testPay(): void
    {
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertEquals($buyer->balance, 0);
        $buyer->deposit($product->price);

        $this->assertEquals($buyer->balance, $product->price);
        $this->assertNotNull($buyer->pay($product));

        $this->assertEquals($buyer->balance, 0);
        $this->assertNull($buyer->safePay($product));
    }

    /**
     * @return void
     */
    public function testRefund(): void
    {
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertEquals($buyer->balance, 0);
        $buyer->deposit($product->price);

        $this->assertEquals($buyer->balance, $product->price);
        $this->assertNotNull($buyer->pay($product));

        $this->assertTrue($buyer->refund($product));
        $this->assertEquals($buyer->balance, $product->price);

        $this->assertFalse($buyer->safeRefund($product));
        $this->assertEquals($buyer->balance, $product->price);

        $this->assertNotNull($buyer->pay($product));
        $this->assertEquals($buyer->balance, 0);
    }

    /**
     * @return void
     */
    public function testForceRefund(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertNotEquals($product->balance, 0);
        $product->withdraw($product->balance);

        $this->assertEquals($buyer->balance, 0);
        $buyer->deposit($product->price);

        $this->assertEquals($buyer->balance, $product->price);

        $buyer->pay($product);
        $this->assertEquals($buyer->balance, 0);
        $this->assertEquals($product->balance, $product->price);

        $product->withdraw($product->balance);
        $this->assertEquals($product->balance, 0);

        $this->assertFalse($buyer->safeRefund($product));
        $this->assertTrue($buyer->forceRefund($product));

        $this->assertEquals($product->balance, -$product->price);
        $this->assertEquals($buyer->balance, $product->price);
        $product->deposit(-$product->balance);
        $buyer->withdraw($buyer->balance);

        $this->assertEquals($product->balance, 0);
        $this->assertEquals($buyer->balance, 0);
    }

}
