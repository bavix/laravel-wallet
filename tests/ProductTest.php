<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;

class ProductTest extends TestCase
{

    /**
     * @return Buyer
     */
    public function getBuyer(): Buyer
    {
        return factory(Buyer::class)->create();
    }

    /**
     * @return void
     */
    public function testPay(): void
    {
        $buyer = $this->getBuyer();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);
        $this->assertEquals($buyer->balance, 0);
        $buyer->deposit($product->price);
        $this->assertEquals($buyer->balance, $product->price);
        $this->assertTrue($buyer->pay($product)->exists);
        $this->assertEquals($buyer->balance, 0);
    }

}
