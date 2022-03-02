<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\Item;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
class GiftTest extends TestCase
{
    public function testGift(): void
    {
        /**
         * @var Buyer $first
         * @var Buyer $second
         * @var Item  $product
         */
        [$first, $second] = BuyerFactory::times(2)->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $first->balanceInt);
        self::assertSame(0, $second->balanceInt);

        $first->deposit($product->getAmountProduct($first));
        self::assertSame($first->balanceInt, $product->getAmountProduct($first));

        $transfer = $first->wallet->gift($second, $product);
        self::assertSame(0, $first->balanceInt);
        self::assertSame(0, $second->balanceInt);
        self::assertNull($first->paid($product, true));
        self::assertNotNull($second->paid($product, true));
        self::assertNull($second->wallet->paid($product));
        self::assertNotNull($second->wallet->paid($product, true));
        self::assertSame(Transfer::STATUS_GIFT, $transfer->status);
    }

    public function testRefund(): void
    {
        /**
         * @var Buyer $first
         * @var Buyer $second
         * @var Item  $product
         */
        [$first, $second] = BuyerFactory::times(2)->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame($first->balanceInt, 0);
        self::assertSame($second->balanceInt, 0);

        $first->deposit($product->getAmountProduct($first));
        self::assertSame($first->balanceInt, $product->getAmountProduct($first));

        $transfer = $first->wallet->gift($second, $product);
        self::assertSame($first->balanceInt, 0);
        self::assertSame($second->balanceInt, 0);
        self::assertSame($transfer->status, Transfer::STATUS_GIFT);

        self::assertFalse($second->wallet->safeRefund($product));
        self::assertTrue($second->wallet->refundGift($product));

        self::assertSame($first->balanceInt, $product->getAmountProduct($first));
        self::assertSame($second->balanceInt, 0);

        self::assertNull($second->wallet->safeGift($first, $product));

        $transfer = $second->wallet->forceGift($first, $product);
        self::assertNotNull($transfer);
        self::assertSame($transfer->status, Transfer::STATUS_GIFT);

        self::assertSame($second->balanceInt, -$product->getAmountProduct($second));

        $second->deposit(-$second->balance);
        self::assertSame($second->balanceInt, 0);

        $first->withdraw($product->getAmountProduct($first));
        self::assertSame($first->balanceInt, 0);

        $product->withdraw($product->balance);
        self::assertSame($product->balanceInt, 0);

        self::assertFalse($first->safeRefundGift($product));
        self::assertTrue($first->forceRefundGift($product));
        self::assertSame($product->balanceInt, -$product->getAmountProduct($second));

        self::assertSame($second->balanceInt, $product->getAmountProduct($second));
        $second->withdraw($second->balance);
        self::assertSame($second->balanceInt, 0);
    }
}
