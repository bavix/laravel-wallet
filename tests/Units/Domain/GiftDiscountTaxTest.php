<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Enums\TransferStatus;
use Bavix\Wallet\External\Api\PurchaseQuery;
use Bavix\Wallet\External\Api\PurchaseQueryHandlerInterface;
use Bavix\Wallet\Services\TaxServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemDiscountTaxFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\ItemDiscountTax;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class GiftDiscountTaxTest extends TestCase
{
    public function testGift(): void
    {
        /**
         * @var Buyer $first
         * @var Buyer $second
         */
        [$first, $second] = BuyerFactory::times(2)->create();
        /** @var ItemDiscountTax $product */
        $product = ItemDiscountTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $first->balanceInt);
        self::assertSame(0, $second->balanceInt);

        $fee = (int) app(TaxServiceInterface::class)->getFee(
            $product,
            $product->getAmountProduct($first) - $product->getPersonalDiscount($first)
        );

        $first->deposit($product->getAmountProduct($first) + $fee);
        self::assertSame($first->balanceInt, $product->getAmountProduct($first) + $fee);

        $transfer = $first->wallet->gift($second, $product);
        self::assertSame($first->balanceInt, $product->getPersonalDiscount($first));
        self::assertSame($second->balanceInt, 0);
        self::assertNull(app(PurchaseQueryHandlerInterface::class)->one(PurchaseQuery::create($first, $product, true)));
        self::assertNotNull(
            app(PurchaseQueryHandlerInterface::class)->one(PurchaseQuery::create($second, $product, true))
        );
        self::assertNull(
            app(PurchaseQueryHandlerInterface::class)->one(PurchaseQuery::create($second->wallet, $product))
        );
        self::assertNotNull(
            app(PurchaseQueryHandlerInterface::class)->one(PurchaseQuery::create($second->wallet, $product, true))
        );
        self::assertSame($transfer->status, TransferStatus::Gift);
    }

    public function testRefund(): void
    {
        /**
         * @var Buyer $first
         * @var Buyer $second
         */
        [$first, $second] = BuyerFactory::times(2)->create();
        /** @var ItemDiscountTax $product */
        $product = ItemDiscountTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame($first->balanceInt, 0);
        self::assertSame($second->balanceInt, 0);

        $fee = (int) app(TaxServiceInterface::class)->getFee(
            $product,
            $product->getAmountProduct($first) - $product->getPersonalDiscount($first)
        );

        $first->deposit($product->getAmountProduct($first) + $fee);
        self::assertSame($first->balanceInt, $product->getAmountProduct($first) + $fee);

        $transfer = $first->wallet->gift($second, $product);
        self::assertSame($first->balance, (string) $product->getPersonalDiscount($first));
        self::assertSame($second->balanceInt, 0);
        self::assertSame($transfer->status, TransferStatus::Gift);

        $first->withdraw($product->getPersonalDiscount($first));
        self::assertSame($first->balanceInt, 0);

        self::assertFalse($second->wallet->safeRefund($product));
        self::assertTrue($second->wallet->refundGift($product));

        self::assertSame(
            $first->balanceInt,
            $product->getAmountProduct($first) - $product->getPersonalDiscount($first)
        );

        $first->withdraw($first->balance);
        self::assertSame($first->balanceInt, 0);
        self::assertSame($second->balanceInt, 0);

        self::assertNull($second->wallet->safeGift($first, $product));

        $secondFee = (int) app(TaxServiceInterface::class)->getFee(
            $product,
            $product->getAmountProduct($second) - $product->getPersonalDiscount($second)
        );

        $transfer = $second->wallet->forceGift($first, $product);
        self::assertNotNull($transfer);
        self::assertSame($transfer->status, TransferStatus::Gift);

        self::assertSame(
            $second->balanceInt,
            -(($product->getAmountProduct($second) + $secondFee) - $product->getPersonalDiscount($second))
        );

        $second->deposit(-$second->balanceInt);
        self::assertSame($second->balanceInt, 0);
        self::assertSame($first->balanceInt, 0);

        $product->withdraw($product->balance);
        self::assertSame($product->balanceInt, 0);

        self::assertFalse($first->safeRefundGift($product));
        self::assertTrue($first->forceRefundGift($product));

        self::assertSame($second->balanceInt, -$product->balanceInt);

        self::assertSame(
            $product->balanceInt,
            -($product->getAmountProduct($second) - $product->getPersonalDiscount($second))
        );

        self::assertSame(
            $second->balanceInt,
            $product->getAmountProduct($second) - $product->getPersonalDiscount($second)
        );

        $second->withdraw($second->balance);
        self::assertSame($second->balanceInt, 0);
    }
}
