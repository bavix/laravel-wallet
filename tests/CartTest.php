<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;
use function count;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $this->assertTrue((bool)$cart->alreadyBuy($buyer));
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

    /**
     * @throws
     */
    public function testModelNotFoundException(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item[] $products
         */
        $this->expectException(ModelNotFoundException::class);
        $buyer = factory(Buyer::class)->create();
        $products = factory(Item::class, 10)->create([
            'quantity' => 10,
        ]);

        $cart = Cart::make();
        $amount = 0;
        for ($i = 0; $i < count($products) - 1; $i++) {
            $rnd = random_int(1, 5);
            $cart->addItem($products[$i], $rnd);
            $buyer->deposit($products[$i]->getAmountProduct() * $rnd);
            $amount += $rnd;
        }

        $this->assertCount($amount, $cart->getItems());

        $transfers = $buyer->payCart($cart);
        $this->assertCount($amount, $transfers);

        $refundCart = Cart::make()->addItems($products); // all goods
        $buyer->refundCart($refundCart);
    }

}
