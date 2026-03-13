<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Enums\TransferStatus;
use Bavix\Wallet\External\Api\PurchaseQuery;
use Bavix\Wallet\External\Api\PurchaseQueryHandlerInterface;
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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @internal
 */
final class CartReceivingTest extends TestCase
{
    public function testCartMeta(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var ItemMeta $product */
        $product = ItemMetaFactory::new()->create([
            'quantity' => 1,
        ]);

        $expected = 'pay';

        $payment = $buyer->createWallet([
            'name' => 'Dollar',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);

        $receiving = $product->createWallet([
            'name' => 'Dollar',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);

        $cart = app(Cart::class)
            ->withItem($product, receiving: $receiving)
            ->withMeta([
                'type' => $expected,
            ]);

        $amount = $cart->getTotal($buyer);

        self::assertSame(0, $buyer->balanceInt);
        self::assertNotNull($payment->deposit($amount));

        $transfers = $payment->payCart($cart);
        self::assertCount(1, $transfers);

        $transfer = current($transfers);

        /** @var Transaction[] $transactions */
        $transactions = [$transfer->deposit, $transfer->withdraw];
        foreach ($transactions as $transaction) {
            self::assertSame($product->price, $transaction->meta['price']);
            self::assertSame($product->name, $transaction->meta['name']);
            self::assertSame($expected, $transaction->meta['type']);
        }

        self::assertSame((int) $amount, $receiving->balanceInt);
        self::assertSame('USD', $receiving->currency);

        self::assertSame(0, $payment->balanceInt);
        self::assertSame('USD', $payment->currency);
    }

    public function testPay(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Collection<int, Item> $products */
        $products = ItemFactory::times(10)->create([
            'quantity' => 1,
        ]);

        $cart = app(Cart::class);
        foreach ($products as $product) {
            $receiving = $product->createWallet([
                'name' => 'Dollar',
                'meta' => [
                    'currency' => 'USD',
                ],
            ]);

            $cart = $cart->withItem($product, pricePerItem: 1, receiving: $receiving);
        }

        self::assertCount(10, $cart->getItems());

        foreach ($cart->getItems() as $product) {
            /** @var Item $product */
            self::assertSame(0, $product->getWallet('dollar')?->balanceInt);
        }

        $payment = $buyer->createWallet([
            'name' => 'Dollar',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);

        $payment->deposit($cart->getTotal($buyer));

        self::assertSame(10, $payment->balanceInt);

        $transfers = $payment->payCart($cart);

        self::assertCount(count($cart), $transfers);
        self::assertTrue((bool) app(PurchaseServiceInterface::class)->already($payment, $cart->getBasketDto()));
        self::assertSame(0, $payment->balanceInt);

        foreach ($transfers as $transfer) {
            self::assertSame(TransferStatus::Paid, $transfer->status);
            self::assertNull($transfer->status_last);
        }

        foreach ($cart->getItems() as $product) {
            /** @var Item $product */
            self::assertSame(1, $product->getWallet('dollar')?->balanceInt);
        }

        self::assertTrue($payment->refundCart($cart));
        foreach ($transfers as $transfer) {
            $transfer->refresh();
            self::assertSame(TransferStatus::Refund, $transfer->status);
            self::assertSame(TransferStatus::Paid, $transfer->status_last);
        }

        self::assertSame(10, $payment->balanceInt);
    }

    public function testIssue1000PurchaseQueryWithReceivingWallet(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
            'price' => 100,
        ]);

        $payment = $buyer->createWallet([
            'name' => 'Dollar',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);

        $receiving = $product->createWallet([
            'name' => 'Dollar',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);

        $cart = app(Cart::class)
            ->withItem($product, 1, null, $receiving);

        $payment->deposit($cart->getTotal($buyer));
        $transfers = $payment->payCart($cart);
        self::assertCount(1, $transfers);

        $handler = app(PurchaseQueryHandlerInterface::class);

        $withoutReceiving = $handler->one(PurchaseQuery::create($payment, $product));
        self::assertNull($withoutReceiving);

        $withReceiving = $handler->one(PurchaseQuery::create($payment, $product, false, $receiving));
        self::assertInstanceOf(Transfer::class, $withReceiving);
        $firstTransfer = reset($transfers);
        self::assertInstanceOf(Transfer::class, $firstTransfer);
        self::assertSame($firstTransfer->getKey(), $withReceiving->getKey());
    }

    public function testIssue1000LegacyPurchaseServiceWithReceivingWallet(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
            'price' => 100,
        ]);

        $payment = $buyer->createWallet([
            'name' => 'Dollar',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);

        $receiving = $product->createWallet([
            'name' => 'Dollar',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);

        $cart = app(Cart::class)
            ->withItem($product, 1, null, $receiving);

        $payment->deposit($cart->getTotal($buyer));
        $transfers = $payment->payCart($cart);
        self::assertCount(1, $transfers);

        $legacy = app(PurchaseServiceInterface::class)
            ->already($payment, $cart->getBasketDto(), false);

        self::assertCount(1, $legacy);

        $firstTransfer = reset($transfers);
        $legacyTransfer = reset($legacy);

        self::assertInstanceOf(Transfer::class, $firstTransfer);
        self::assertInstanceOf(Transfer::class, $legacyTransfer);
        self::assertSame($firstTransfer->getKey(), $legacyTransfer->getKey());
    }

    public function testPurchaseQueryFallbackToTransfersWhenLedgerMissing(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
            'price' => 100,
        ]);

        $payment = $buyer->createWallet([
            'name' => 'Dollar',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);

        $receiving = $product->createWallet([
            'name' => 'Dollar',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);

        $cart = app(Cart::class)
            ->withItem($product, 1, null, $receiving);

        $payment->deposit($cart->getTotal($buyer));
        $transfers = $payment->payCart($cart);
        self::assertCount(1, $transfers);

        $firstTransfer = reset($transfers);
        self::assertInstanceOf(Transfer::class, $firstTransfer);

        /** @var string $purchaseTable */
        $purchaseTable = config('wallet.purchase.table', 'purchase');
        DB::table($purchaseTable)
            ->where('transfer_id', $firstTransfer->getKey())
            ->delete();

        $queryHandler = app(PurchaseQueryHandlerInterface::class);
        $matched = $queryHandler->one(PurchaseQuery::create($payment, $product, false, $receiving));

        self::assertInstanceOf(Transfer::class, $matched);
        self::assertSame($firstTransfer->getKey(), $matched->getKey());
    }

    public function testRefundSucceedsWhenPurchaseLedgerRecordMissing(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
            'price' => 100,
        ]);

        $cart = app(Cart::class)->withItem($product);

        $buyer->deposit($cart->getTotal($buyer));
        $transfers = $buyer->payCart($cart);
        self::assertCount(1, $transfers);

        $firstTransfer = reset($transfers);
        self::assertInstanceOf(Transfer::class, $firstTransfer);

        /** @var string $purchaseTable */
        $purchaseTable = config('wallet.purchase.table', 'purchase');
        DB::table($purchaseTable)
            ->where('transfer_id', $firstTransfer->getKey())
            ->delete();

        self::assertTrue($buyer->refundCart($cart));

        $firstTransfer->refresh();
        self::assertSame(TransferStatus::Refund, $firstTransfer->status);
        self::assertSame(TransferStatus::Paid, $firstTransfer->status_last);
    }
}
