<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;

class ProductTest extends TestCase
{

    /**
     * @return void
     */
    public function testPay(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertEquals($buyer->balance, 0);
        $buyer->deposit($product->price);

        $this->assertEquals($buyer->balance, $product->price);
        $transfer = $buyer->pay($product);
        $this->assertNotNull($transfer);

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        $this->assertInstanceOf(Transaction::class, $withdraw);
        $this->assertInstanceOf(Transaction::class, $deposit);

        $this->assertInstanceOf(Buyer::class, $withdraw->payable);
        $this->assertInstanceOf(Item::class, $deposit->payable);

        $this->assertEquals($buyer->getKey(), $withdraw->payable->getKey());
        $this->assertEquals($product->getKey(), $deposit->payable->getKey());

        $this->assertInstanceOf(Buyer::class, $transfer->from);
        $this->assertInstanceOf(Item::class, $transfer->to);

        $this->assertEquals($buyer->getKey(), $transfer->from->getKey());
        $this->assertEquals($product->getKey(), $transfer->to->getKey());

        $this->assertEquals($buyer->balance, 0);
        $this->assertNull($buyer->safePay($product));
    }

    /**
     * @return void
     */
    public function testRefund(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
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
        $this->assertEquals($product->balance, $product->price);
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

    /**
     * @return void
     * @expectedException \Bavix\Wallet\Exceptions\ProductEnded
     */
    public function testOutOfStock(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $buyer->deposit($product->price);
        $buyer->pay($product);
        $buyer->pay($product);
    }

    /**
     * @return void
     */
    public function testForcePay(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertEquals($buyer->balance, 0);
        $buyer->forcePay($product);

        $this->assertEquals($buyer->balance, -$product->price);

        $buyer->deposit(-$buyer->balance);
        $this->assertEquals($buyer->balance, 0);
    }

    /**
     * @return void
     */
    public function testPayFree(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertEquals($buyer->balance, 0);

        $transfer = $buyer->payFree($product);
        $this->assertEquals($transfer->deposit->type, Transaction::TYPE_DEPOSIT);
        $this->assertEquals($transfer->withdraw->type, Transaction::TYPE_WITHDRAW);

        $this->assertEquals($buyer->balance, 0);
        $this->assertEquals($product->balance, 0);

        $buyer->refund($product);
        $this->assertEquals($buyer->balance, 0);
        $this->assertEquals($product->balance, 0);
    }

    /**
     * @return void
     * @expectedException \Bavix\Wallet\Exceptions\ProductEnded
     */
    public function testPayFreeOutOfStock(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item $product
         */
        $buyer = factory(Buyer::class)->create();
        $product = factory(Item::class)->create([
            'quantity' => 1,
        ]);

        $this->assertNotNull($buyer->payFree($product));
        $buyer->payFree($product);
    }

}
