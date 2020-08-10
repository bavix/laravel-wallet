<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

        $cart = app(Cart::class)->addItems($products);
        foreach ($cart->getItems() as $product) {
            self::assertEquals($product->balance, 0);
        }

        self::assertEquals($buyer->balance, $buyer->wallet->balance);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));
        self::assertEquals($buyer->balance, $buyer->wallet->balance);

        $transfers = $buyer->payCart($cart);
        self::assertCount(count($cart), $transfers);
        self::assertTrue((bool)$cart->alreadyBuy($buyer));
        self::assertEquals($buyer->balance, 0);

        foreach ($transfers as $transfer) {
            self::assertEquals($transfer->status, Transfer::STATUS_PAID);
        }

        foreach ($cart->getItems() as $product) {
            self::assertEquals($product->balance, $product->getAmountProduct($buyer));
        }

        self::assertTrue($buyer->refundCart($cart));
        foreach ($transfers as $transfer) {
            $transfer->refresh();
            self::assertEquals($transfer->status, Transfer::STATUS_REFUND);
        }
    }

    /**
     * @throws
     */
    public function testCartQuantity(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item[] $products
         */
        $buyer = factory(Buyer::class)->create();
        $products = factory(Item::class, 10)->create([
            'quantity' => 10,
        ]);

        $cart = app(Cart::class);
        $amount = 0;
        for ($i = 0; $i < count($products) - 1; $i++) {
            $rnd = random_int(1, 5);
            $cart->addItem($products[$i], $rnd);
            $buyer->deposit($products[$i]->getAmountProduct($buyer) * $rnd);
            $amount += $rnd;
        }

        self::assertCount($amount, $cart->getItems());

        $transfers = $buyer->payCart($cart);
        self::assertCount($amount, $transfers);

        self::assertTrue($buyer->refundCart($cart));
        foreach ($transfers as $transfer) {
            $transfer->refresh();
            self::assertEquals($transfer->status, Transfer::STATUS_REFUND);
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

        $cart = app(Cart::class);
        $total = 0;
        for ($i = 0; $i < count($products) - 1; $i++) {
            $rnd = random_int(1, 5);
            $cart->addItem($products[$i], $rnd);
            $buyer->deposit($products[$i]->getAmountProduct($buyer) * $rnd);
            $total += $rnd;
        }

        self::assertCount($total, $cart->getItems());

        $transfers = $buyer->payCart($cart);
        self::assertCount($total, $transfers);

        $refundCart = app(Cart::class)
            ->addItems($products); // all goods

        $buyer->refundCart($refundCart);
    }

}
