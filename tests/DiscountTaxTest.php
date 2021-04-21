<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Factories\ItemDiscountTaxFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\Item;
use Bavix\Wallet\Test\Models\ItemDiscountTax;

/**
 * @internal
 */
class DiscountTaxTest extends TestCase
{
    public function testPay(): void
    {
        /**
         * @var Buyer           $buyer
         * @var ItemDiscountTax $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountTaxFactory::new()->create();

        self::assertEquals(0, $buyer->balance);
        $fee = app(WalletService::class)->fee($product, $product->getAmountProduct($buyer));
        $buyer->deposit($product->getAmountProduct($buyer) + $fee);

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer) + $fee);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals(Transfer::STATUS_PAID, $transfer->status);

        self::assertEquals(
            $buyer->balance,
            $product->getPersonalDiscount($buyer)
        );

        self::assertEquals(
            $transfer->discount,
            $product->getPersonalDiscount($buyer)
        );

        self::assertEquals($transfer->fee, $fee);

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertInstanceOf(Transaction::class, $withdraw);
        self::assertInstanceOf(Transaction::class, $deposit);

        self::assertInstanceOf(Buyer::class, $withdraw->payable);
        self::assertInstanceOf(Item::class, $deposit->payable);

        self::assertEquals($buyer->getKey(), $withdraw->payable->getKey());
        self::assertEquals($product->getKey(), $deposit->payable->getKey());

        self::assertInstanceOf(Buyer::class, $transfer->from->holder);
        self::assertInstanceOf(Wallet::class, $transfer->from);
        self::assertInstanceOf(Item::class, $transfer->to);
        self::assertInstanceOf(Wallet::class, $transfer->to->wallet);

        self::assertEquals($buyer->wallet->getKey(), $transfer->from->getKey());
        self::assertEquals($buyer->getKey(), $transfer->from->holder->getKey());
        self::assertEquals($product->getKey(), $transfer->to->getKey());
    }

    public function testRefund(): void
    {
        /**
         * @var Buyer           $buyer
         * @var ItemDiscountTax $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountTaxFactory::new()->create();

        self::assertEquals($buyer->balance, 0);
        $discount = app(WalletService::class)->discount($buyer, $product);
        $fee = app(WalletService::class)->fee($product, $product->getAmountProduct($buyer));
        $buyer->deposit($product->getAmountProduct($buyer) + $fee - $discount);

        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer) + $fee - $discount);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals($transfer->status, Transfer::STATUS_PAID);

        self::assertEquals(
            $transfer->discount,
            $product->getPersonalDiscount($buyer)
        );

        self::assertEquals($transfer->fee, $fee);

        self::assertTrue($buyer->refund($product));
        self::assertEquals(
            $buyer->balance,
            $product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer)
        );

        self::assertEquals($product->balance, 0);

        $transfer->refresh();
        self::assertEquals($transfer->status, Transfer::STATUS_REFUND);

        self::assertFalse($buyer->safeRefund($product));
        self::assertEquals(
            $buyer->balance,
            $product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer)
        );

        self::assertNull($buyer->safePay($product));
        $buyer->deposit($fee);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertEquals(0, $buyer->balance);
        self::assertEquals(
            $product->balance,
            $product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer)
        );

        self::assertEquals($transfer->status, Transfer::STATUS_PAID);

        self::assertTrue($buyer->refund($product));
        self::assertEquals(
            $buyer->balance,
            $product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer)
        );

        self::assertEquals($product->balance, 0);

        $transfer->refresh();
        self::assertEquals($transfer->status, Transfer::STATUS_REFUND);
    }

    public function testForceRefund(): void
    {
        /**
         * @var Buyer           $buyer
         * @var ItemDiscountTax $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountTaxFactory::new()->create();

        self::assertEquals(0, $buyer->balance);
        $discount = app(WalletService::class)->discount($buyer, $product);
        $fee = app(WalletService::class)->fee($product, $product->getAmountProduct($buyer));
        $buyer->deposit($product->getAmountProduct($buyer) + $fee - $discount);

        $paidPrice = $buyer->balance;
        self::assertEquals($buyer->balance, $product->getAmountProduct($buyer) + $fee - $discount);

        $transfer = $buyer->pay($product);
        self::assertEquals(0, $buyer->balance);

        self::assertEquals(
            $product->balance,
            -$transfer->withdraw->amount - $fee
        );

        self::assertEquals(
            $transfer->discount,
            $product->getPersonalDiscount($buyer)
        );

        self::assertEquals($transfer->fee, $fee);

        $product->withdraw($product->balance);
        self::assertEquals($product->balance, 0);

        self::assertFalse($buyer->safeRefund($product));
        self::assertTrue($buyer->forceRefund($product));

        self::assertEquals($paidPrice - $fee, -$product->balance);
        self::assertEquals(
            $product->balance,
            -($product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer))
        );

        self::assertEquals($paidPrice - $fee, $buyer->balance);
        $product->deposit(-$product->balance);
        $buyer->withdraw($buyer->balance);

        self::assertEquals($product->balance, 0);
        self::assertEquals($buyer->balance, 0);
    }

    public function testOutOfStock(): void
    {
        $this->expectException(ProductEnded::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.product_stock'));

        /**
         * @var Buyer           $buyer
         * @var ItemDiscountTax $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        $fee = app(WalletService::class)->fee($product, $product->getAmountProduct($buyer));
        $buyer->deposit($product->getAmountProduct($buyer) + $fee);
        $buyer->pay($product);
        $buyer->pay($product);
    }

    public function testForcePay(): void
    {
        /**
         * @var Buyer           $buyer
         * @var ItemDiscountTax $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals($buyer->balance, 0);
        $buyer->forcePay($product);

        $fee = app(WalletService::class)->fee($product, $product->getAmountProduct($buyer));
        self::assertEquals(
            $buyer->balance,
            -($product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer)) - $fee
        );

        $buyer->deposit(-$buyer->balance);
        self::assertEquals($buyer->balance, 0);
    }

    public function testPayFree(): void
    {
        /**
         * @var Buyer           $buyer
         * @var ItemDiscountTax $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertEquals($buyer->balance, 0);

        $transfer = $buyer->payFree($product);
        self::assertEquals($transfer->deposit->type, Transaction::TYPE_DEPOSIT);
        self::assertEquals($transfer->withdraw->type, Transaction::TYPE_WITHDRAW);

        self::assertEquals($buyer->balance, 0);
        self::assertEquals($product->balance, 0);

        $buyer->refund($product);
        self::assertEquals($buyer->balance, 0);
        self::assertEquals($product->balance, 0);
    }

    public function testFreePay(): void
    {
        /**
         * @var Buyer           $buyer
         * @var ItemDiscountTax $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        $buyer->forceWithdraw(1000);
        self::assertEquals($buyer->balance, -1000);

        $transfer = $buyer->payFree($product);
        self::assertEquals($transfer->deposit->type, Transaction::TYPE_DEPOSIT);
        self::assertEquals($transfer->withdraw->type, Transaction::TYPE_WITHDRAW);

        self::assertEquals(
            $transfer->discount,
            $product->getPersonalDiscount($buyer)
        );

        self::assertEquals($transfer->fee, 0);

        self::assertEquals($buyer->balance, -1000);
        self::assertEquals($product->balance, 0);

        $buyer->refund($product);
        self::assertEquals($buyer->balance, -1000);
        self::assertEquals($product->balance, 0);
    }

    public function testPayFreeOutOfStock(): void
    {
        $this->expectException(ProductEnded::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.product_stock'));

        /**
         * @var Buyer           $buyer
         * @var ItemDiscountTax $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertNotNull($buyer->payFree($product));
        $buyer->payFree($product);
    }
}
