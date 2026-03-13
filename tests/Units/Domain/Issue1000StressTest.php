<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\External\Api\PurchaseQuery;
use Bavix\Wallet\External\Api\PurchaseQueryHandlerInterface;
use Bavix\Wallet\Services\PurchaseServiceInterface;
use Bavix\Wallet\Test\Infra\PackageModels\Transfer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class Issue1000StressTest extends TestCase
{
    use StressTestSetupTrait;

    public function testIssue1000OnMultipleWalletsAndProducts(): void
    {
        $queryHandler = app(PurchaseQueryHandlerInterface::class);
        $legacyPurchaseService = app(PurchaseServiceInterface::class);

        for ($walletIndex = 0; $walletIndex < 10; $walletIndex++) {
            $buyer = $this->createBuyerWithPaymentWallet($walletIndex);
            $payment = $buyer->wallet;

            $productsCount = $this->getProductsCountForWallet($walletIndex);
            $cart = $this->createCartWithProductsAndReceivingWallets($walletIndex, $buyer);
            $expectedTransferByReceiving = [];

            foreach ($cart->getBasketDto()->items() as $itemDto) {
                $product = $itemDto->getProduct();
                $receiving = $itemDto->getReceiving();
                self::assertInstanceOf(\Bavix\Wallet\Test\Infra\Models\Item::class, $product);
                self::assertInstanceOf(\Bavix\Wallet\Test\Infra\PackageModels\Wallet::class, $receiving);

                $expectedTransferByReceiving[$receiving->id] = [
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
