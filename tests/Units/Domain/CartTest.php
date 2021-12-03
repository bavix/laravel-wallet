<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Exceptions\CartEmptyException;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Services\PurchaseServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemMetaFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\Item;
use Bavix\Wallet\Test\Infra\Models\ItemMeta;
use Bavix\Wallet\Test\Infra\PackageModels\Transaction;
use Bavix\Wallet\Test\Infra\TestCase;
use function count;

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

        self::assertSame(0, $buyer->balanceInt);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));

        $transfers = $buyer->payCart($cart);
        self::assertCount(1, $transfers);

        $transfer = current($transfers);

        /** @var Transaction[] $transactions */
        $transactions = [$transfer->deposit, $transfer->withdraw];
        foreach ($transactions as $transaction) {
            self::assertSame($product->price, $transaction->meta['price']);
            self::assertSame($product->name, $transaction->meta['name']);
            self::assertSame($expected, $transaction->meta['type']);
        }
    }

    public function testCartGetBasketDtoCartEmpty(): void
    {
        $this->expectException(CartEmptyException::class);
        $this->expectExceptionCode(ExceptionInterface::CART_EMPTY);
        app(Cart::class)->getBasketDto();
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

        self::assertSame(0, $buyer->balanceInt);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));

        $transfers = $buyer->payCart($cart);
        self::assertCount(1, $transfers);

        $transfer = current($transfers);

        /** @var Transaction[] $transactions */
        $transactions = [$transfer->deposit, $transfer->withdraw];
        foreach ($transactions as $transaction) {
            self::assertCount(1, $transaction->meta);
            self::assertSame($expected, $transaction->meta['type']);
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
            self::assertSame(0, $product->getBalanceIntAttribute());
        }

        self::assertSame($buyer->balance, $buyer->wallet->balance);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));
        self::assertSame($buyer->balance, $buyer->wallet->balance);

        $transfers = $buyer->payCart($cart);
        self::assertCount(count($cart), $transfers);
        self::assertTrue((bool) app(PurchaseServiceInterface::class)->already($buyer, $cart->getBasketDto()));
        self::assertSame(0, $buyer->balanceInt);

        foreach ($transfers as $transfer) {
            self::assertSame(Transfer::STATUS_PAID, $transfer->status);
        }

        foreach ($cart->getItems() as $product) {
            self::assertSame($product->balance, (string) $product->getAmountProduct($buyer));
        }

        self::assertTrue($buyer->refundCart($cart));
        foreach ($transfers as $transfer) {
            $transfer->refresh();
            self::assertSame(Transfer::STATUS_REFUND, $transfer->status);
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
        $price = 0;
        for ($i = 0; $i < count($products) - 1; ++$i) {
            $rnd = random_int(1, 5);
            $cart->addItem($products[$i], $rnd);
            $price += $products[$i]->getAmountProduct($buyer) * $rnd;
            $amount += $rnd;
        }

        $buyer->deposit($price);
        self::assertCount($amount, $cart->getItems());

        $transfers = $buyer->payCart($cart);
        self::assertCount($amount, $transfers);

        self::assertTrue($buyer->refundCart($cart));
        foreach ($transfers as $transfer) {
            $transfer->refresh();
            self::assertSame(Transfer::STATUS_REFUND, $transfer->status);
        }
    }

    /**
     * @throws
     */
    public function testModelNotFoundException(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionCode(ExceptionInterface::MODEL_NOT_FOUND);
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
        self::assertCount(count($products) - 1, $cart->getBasketDto()->items());
        self::assertCount($total, $cart->getBasketDto()->cursor());
        self::assertSame($total, $cart->getBasketDto()->total());

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

            self::assertSame($total[$product->getKey()], $count);
        }
    }

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/279
     */
    public function testWithdrawal(): void
    {
        $transactionLevel = Buyer::query()->getConnection()->transactionLevel();
        self::assertSame(0, $transactionLevel);

        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create(['quantity' => 1]);

        $cart = app(Cart::class);
        $cart->addItem($product, 1);

        foreach ($cart->getItems() as $item) {
            self::assertSame(0, $item->getBalanceIntAttribute());
        }

        $math = app(MathServiceInterface::class);

        self::assertSame($buyer->balance, $buyer->wallet->balance);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));
        self::assertSame(0, $math->compare($cart->getTotal($buyer), $buyer->balance));
        self::assertSame($buyer->balance, $buyer->wallet->balance);

        $transfers = $buyer->payCart($cart);
        self::assertCount(count($cart), $transfers);
        self::assertTrue((bool) app(PurchaseServiceInterface::class)->already($buyer, $cart->getBasketDto()));
        self::assertSame(0, $buyer->balanceInt);

        foreach ($transfers as $transfer) {
            self::assertSame(Transfer::STATUS_PAID, $transfer->status);
        }

        foreach ($cart->getItems() as $product) {
            self::assertSame($product->balance, (string) $product->getAmountProduct($buyer));
        }

        self::assertTrue($buyer->refundCart($cart));
        self::assertSame(0, $math->compare($cart->getTotal($buyer), $buyer->balance));
        self::assertSame($transactionLevel, $buyer->getConnection()->transactionLevel()); // check case #1

        foreach ($transfers as $transfer) {
            $transfer->refresh();
            self::assertSame(Transfer::STATUS_REFUND, $transfer->status);
        }

        $withdraw = $buyer->withdraw($buyer->balance); // problem place... withdrawal
        self::assertNotNull($withdraw);
        self::assertSame(0, $buyer->balanceInt);

        // check in the database
        $balance = $buyer->wallet::query()
            ->whereKey($buyer->wallet->getKey())
            ->getQuery()
            ->value('balance')
        ;

        self::assertSame(0, (int) $balance);
    }
}
