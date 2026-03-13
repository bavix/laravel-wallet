<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\External\Api\PurchaseQuery;
use Bavix\Wallet\External\Api\PurchaseQueryHandlerInterface;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Services\PurchaseServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\Item;
use Bavix\Wallet\Test\Infra\PackageModels\Transfer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class Issue1000StressTest extends TestCase
{
    public function testIssue1000OnMultipleWalletsAndProducts(): void
    {
        $queryHandler = app(PurchaseQueryHandlerInterface::class);
        $legacyPurchaseService = app(PurchaseServiceInterface::class);

        for ($walletIndex = 0; $walletIndex < 10; $walletIndex++) {
            /** @var Buyer $buyer */
            $buyer = BuyerFactory::new()->create();

            $payment = $buyer->createWallet([
                'name' => 'Dollar '.$walletIndex,
                'meta' => [
                    'currency' => 'USD',
                ],
            ]);

            $productsCount = 3 + $walletIndex % 3;
            $cart = app(Cart::class);
            $expectedTransferByReceiving = [];

            for ($productIndex = 0; $productIndex < $productsCount; $productIndex++) {
                /** @var Item $product */
                $product = ItemFactory::new()->create([
                    'quantity' => 1,
                    'price' => 100 + $walletIndex * 10 + $productIndex,
                ]);

                $receiving = $product->createWallet([
                    'name' => 'Dollar '.$walletIndex.'-'.$productIndex,
                    'meta' => [
                        'currency' => 'USD',
                    ],
                ]);

                $cart = $cart->withItem($product, 1, null, $receiving);
                $expectedTransferByReceiving[$receiving->getKey()] = [
                    'product' => $product,
                    'receiving' => $receiving,
                ];
            }

            $payment->deposit($cart->getTotal($buyer));
            $transfers = $payment->payCart($cart);
            self::assertCount($productsCount, $transfers);

            $transferByToId = [];
            foreach ($transfers as $transfer) {
                self::assertInstanceOf(Transfer::class, $transfer);
                $transferByToId[$transfer->to_id] = $transfer;
            }

            foreach ($expectedTransferByReceiving as $receivingId => $pair) {
                /** @var Item $product */
                $product = $pair['product'];
                $receiving = $pair['receiving'];

                $withoutReceiving = $queryHandler->one(PurchaseQuery::create($payment, $product));
                self::assertNull($withoutReceiving);

                $withReceiving = $queryHandler->one(PurchaseQuery::create($payment, $product, false, $receiving));
                self::assertInstanceOf(Transfer::class, $withReceiving);

                $expected = $transferByToId[$receivingId] ?? null;
                self::assertInstanceOf(Transfer::class, $expected);
                self::assertSame($expected->getKey(), $withReceiving->getKey());
            }

            $legacy = $legacyPurchaseService->already($payment, $cart->getBasketDto(), false);
            self::assertCount($productsCount, $legacy);
        }
    }
}
