<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;
use function count;

class CartTest extends TestCase
{

    /**
     * @return void
     */
    public function testPay(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item[] $products
         */
        $buyer = factory(Buyer::class)->create();
        $products = factory(Item::class, 10)->create([
            'quantity' => 1,
        ]);

        $cart = Cart::make()->addItems($products);
        foreach ($cart->getItems() as $product) {
            $this->assertEquals($product->balance, 0);
        }

        $this->assertEquals($buyer->balance, $buyer->wallet->balance);
        $this->assertNotNull($buyer->deposit($cart->getTotal()));
        $this->assertEquals($buyer->balance, $buyer->wallet->balance);

        $transfers = $buyer->payCart($cart);
        $this->assertCount(count($cart), $transfers);
        $this->assertTrue((bool)$cart->hasPaid($buyer));
        $this->assertEquals($buyer->balance, 0);

        foreach ($transfers as $transfer) {
            $this->assertEquals($transfer->status, Transfer::STATUS_PAID);
        }

        foreach ($cart->getItems() as $product) {
            $this->assertEquals($product->balance, $product->getAmountProduct());
        }

        $this->assertTrue($buyer->refundCart($cart));
        foreach ($transfers as $transfer) {
            $transfer->refresh();
            $this->assertEquals($transfer->status, Transfer::STATUS_REFUND);
        }
    }

}
