<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Test\Common\Models\Transaction;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Factories\ItemFactory;
use Bavix\Wallet\Test\Factories\ItemMetaFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;
use Bavix\Wallet\Test\Models\ItemMeta;
use function count;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @internal
 */
class CartTest extends TestCase
{
    public function testCartMeta(): void
    {
        /**
         * @var Buyer    $buyer
         * @var ItemMeta $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemMetaFactory::new()->create([
            'quantity' => 1,
        ]);

        $expected = 'pay';

        $cart = app(Cart::class)
            ->addItems([$product])
            ->setMeta(['type' => $expected])
        ;

        self::assertEquals(0, $buyer->balance);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));

        $transfers = $buyer->payCart($cart);
        self::assertCount(1, $transfers);

        $transfer = current($transfers);

        /** @var Transaction[] $transactions */
        $transactions = [$transfer->deposit, $transfer->withdraw];
        foreach ($transactions as $transaction) {
            self::assertEquals($product->price, $transaction->meta['price']);
            self::assertEquals($product->name, $transaction->meta['name']);
            self::assertEquals($expected, $transaction->meta['type']);
        }
    }

    public function testCartMetaItemNoMeta(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        $expected = 'pay';

        $cart = app(Cart::class)
            ->addItems([$product])
            ->setMeta(['type' => $expected])
        ;

        self::assertEquals(0, $buyer->balance);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));

        $transfers = $buyer->payCart($cart);
        self::assertCount(1, $transfers);

        $transfer = current($transfers);

        /** @var Transaction[] $transactions */
        $transactions = [$transfer->deposit, $transfer->withdraw];
        foreach ($transactions as $transaction) {
            self::assertCount(1, $transaction->meta);
            self::assertEquals($expected, $transaction->meta['type']);
        }
    }

    public function testPay(): void
    {
        /**
         * @var Buyer  $buyer
         * @var Item[] $products
         */
        $buyer = BuyerFactory::new()->create();
        $products = ItemFactory::times(10)->create([
            'quantity' => 1,
        ]);

        $cart = app(Cart::class)->addItems($products);
        foreach ($cart->getItems() as $product) {
            self::assertEquals(0, $product->balance);
        }

        self::assertEquals($buyer->balance, $buyer->wallet->balance);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));
        self::assertEquals($buyer->balance, $buyer->wallet->balance);

        $transfers = $buyer->payCart($cart);
        self::assertCount(count($cart), $transfers);
        self::assertTrue((bool) $cart->alreadyBuy($buyer));
        self::assertEquals(0, $buyer->balance);

        foreach ($transfers as $transfer) {
            self::assertEquals(Transfer::STATUS_PAID, $transfer->status);
        }

        foreach ($cart->getItems() as $product) {
            self::assertEquals($product->balance, $product->getAmountProduct($buyer));
        }

        self::assertTrue($buyer->refundCart($cart));
        foreach ($transfers as $transfer) {
            $transfer->refresh();
            self::assertEquals(Transfer::STATUS_REFUND, $transfer->status);
        }
    }

    /**
     * @throws
     */
    public function testCartQuantity(): void
    {
        /**
         * @var Buyer  $buyer
         * @var Item[] $products
         */
        $buyer = BuyerFactory::new()->create();
        $products = ItemFactory::times(10)->create([
            'quantity' => 10,
        ]);

        $cart = app(Cart::class);
        $amount = 0;
        for ($i = 0; $i < count($products) - 1; ++$i) {
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
            self::assertEquals(Transfer::STATUS_REFUND, $transfer->status);
        }
    }

    /**
     * @throws
     */
    public function testModelNotFoundException(): void
    {
        /**
         * @var Buyer  $buyer
         * @var Item[] $products
         */
        $this->expectException(ModelNotFoundException::class);
        $buyer = BuyerFactory::new()->create();
        $products = ItemFactory::times(10)->create([
            'quantity' => 10,
        ]);

        $cart = app(Cart::class);
        $total = 0;
        for ($i = 0; $i < count($products) - 1; ++$i) {
            $rnd = random_int(1, 5);
            $cart->addItem($products[$i], $rnd);
            $buyer->deposit($products[$i]->getAmountProduct($buyer) * $rnd);
            $total += $rnd;
        }

        self::assertCount($total, $cart->getItems());

        $transfers = $buyer->payCart($cart);
        self::assertCount($total, $transfers);

        $refundCart = app(Cart::class)
            ->addItems($products) // all goods
        ;

        $buyer->refundCart($refundCart);
    }

    /**
     * @throws
     */
    public function testBoughtGoods(): void
    {
        /**
         * @var Buyer  $buyer
         * @var Item[] $products
         */
        $buyer = BuyerFactory::new()->create();
        $products = ItemFactory::times(10)->create([
            'quantity' => 10,
        ]);

        $cart = app(Cart::class);
        $total = [];
        foreach ($products as $product) {
            $quantity = random_int(1, 5);
            $cart->addItem($product, $quantity);
            $buyer->deposit($product->getAmountProduct($buyer) * $quantity);
            $total[$product->getKey()] = $quantity;
        }

        $transfers = $buyer->payCart($cart);
        self::assertCount(array_sum($total), $transfers);

        foreach ($products as $product) {
            $count = $product
                ->boughtGoods([$buyer->wallet->getKey()])
                ->count()
            ;

            self::assertEquals($total[$product->getKey()], $count);
        }
    }

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/279
     */
    public function testWithdrawal(): void
    {
        $transactionLevel = app(DbService::class)
            ->connection()
            ->transactionLevel()
        ;

        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create(['quantity' => 1]);

        $cart = app(Cart::class);
        $cart->addItem($product, 1);

        foreach ($cart->getItems() as $item) {
            self::assertEquals(0, $item->balance);
        }

        $math = app(MathInterface::class);

        self::assertEquals($buyer->balance, $buyer->wallet->balance);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));
        self::assertEquals(0, $math->compare($cart->getTotal($buyer), $buyer->balance));
        self::assertEquals($buyer->balance, $buyer->wallet->balance);

        $transfers = $buyer->payCart($cart);
        self::assertCount(count($cart), $transfers);
        self::assertTrue((bool) $cart->alreadyBuy($buyer));
        self::assertEquals(0, $buyer->balance);

        foreach ($transfers as $transfer) {
            self::assertEquals(Transfer::STATUS_PAID, $transfer->status);
        }

        foreach ($cart->getItems() as $product) {
            self::assertEquals($product->balance, $product->getAmountProduct($buyer));
        }

        self::assertTrue($buyer->refundCart($cart));
        self::assertEquals(0, $math->compare($cart->getTotal($buyer), $buyer->balance));
        self::assertEquals($transactionLevel, app(DbService::class)->connection()->transactionLevel()); // check case #1

        foreach ($transfers as $transfer) {
            $transfer->refresh();
            self::assertEquals(Transfer::STATUS_REFUND, $transfer->status);
        }

        $withdraw = $buyer->withdraw($buyer->balance); // problem place... withdrawal
        self::assertNotNull($withdraw);
        self::assertEquals(0, $buyer->balance);

        // check in the database
        $balance = $buyer->wallet::query()
            ->whereKey($buyer->wallet->getKey())
            ->getQuery()
            ->value('balance')
        ;

        self::assertEquals(0, $balance);
    }
}
