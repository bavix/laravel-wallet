<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\Item;

trait StressTestSetupTrait
{
    private function createBuyerWithPaymentWallet(int $walletIndex): Buyer
    {
        $buyer = BuyerFactory::new()->create();
        if (! $buyer instanceof Buyer) {
            throw new \RuntimeException('Buyer factory did not return Buyer instance');
        }

        $buyer->createWallet([
            'name' => 'Dollar '.$walletIndex,
            'meta' => [
                'currency' => 'USD',
            ],
        ]);

        return $buyer;
    }

    private function createCartWithProductsAndReceivingWallets(int $walletIndex, Buyer $buyer): Cart
    {
        $productsCount = 3 + $walletIndex % 3;
        $cart = app(Cart::class);

        for ($productIndex = 0; $productIndex < $productsCount; $productIndex++) {
            $product = ItemFactory::new()->create([
                'quantity' => 1,
                'price' => 100 + $walletIndex * 10 + $productIndex,
            ]);
            if (! $product instanceof Item) {
                throw new \RuntimeException('Item factory did not return Item instance');
            }

            $receiving = $product->createWallet([
                'name' => 'Dollar '.$walletIndex.'-'.$productIndex,
                'meta' => [
                    'currency' => 'USD',
                ],
            ]);

            $cart = $cart->withItem($product, 1, null, $receiving);
        }

        return $cart;
    }

    private function getProductsCountForWallet(int $walletIndex): int
    {
        return 3 + $walletIndex % 3;
    }

    private function getProductPrice(int $walletIndex, int $productIndex): int
    {
        return 100 + $walletIndex * 10 + $productIndex;
    }
}
