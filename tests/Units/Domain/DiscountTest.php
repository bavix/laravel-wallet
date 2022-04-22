<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemDiscountFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\Item;
use Bavix\Wallet\Test\Infra\Models\ItemDiscount;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class DiscountTest extends TestCase
{
    public function testPay(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create();

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertSame($buyer->balanceInt, (int) $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertSame(Transfer::STATUS_PAID, $transfer->status);

        self::assertSame($buyer->balanceInt, $product->getPersonalDiscount($buyer));

        self::assertSame((int) $transfer->discount, $product->getPersonalDiscount($buyer));

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

        self::assertSame($buyer->getKey(), $withdraw->payable->getKey());
        self::assertSame($product->getKey(), $deposit->payable->getKey());

        self::assertInstanceOf(Buyer::class, $transfer->from->holder);
        self::assertInstanceOf(Wallet::class, $transfer->from);
        self::assertInstanceOf(Item::class, $transfer->to->holder);
        self::assertInstanceOf(Wallet::class, $transfer->to->wallet);

        self::assertSame($buyer->wallet->getKey(), $transfer->from->getKey());
        self::assertSame($buyer->getKey(), $transfer->from->holder->getKey());
        self::assertSame($product->wallet->getKey(), $transfer->to->getKey());
        self::assertSame($product->getKey(), $transfer->to->holder->getKey());
    }

    public function testItemTransactions(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create();

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertSame($buyer->balanceInt, (int) $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertSame(Transfer::STATUS_PAID, $transfer->status);

        self::assertSame($buyer->balanceInt, $product->getPersonalDiscount($buyer));

        self::assertSame((int) $transfer->discount, $product->getPersonalDiscount($buyer));

        /**
         * @var Transaction $withdraw
         * @var Transaction $deposit
         */
        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertInstanceOf(Transaction::class, $withdraw);
        self::assertInstanceOf(Transaction::class, $deposit);

        self::assertTrue($withdraw->is(
            $buyer->transactions()
                ->where('type', Transaction::TYPE_WITHDRAW)
                ->latest()
                ->first()
        ));

        self::assertTrue($deposit->is($product->transactions()->latest()->first()));
    }

    public function testRefund(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertSame($buyer->balanceInt, (int) $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertSame(Transfer::STATUS_PAID, $transfer->status);

        self::assertSame((int) $transfer->discount, $product->getPersonalDiscount($buyer));

        self::assertTrue($buyer->refund($product));
        self::assertSame($buyer->balanceInt, (int) $product->getAmountProduct($buyer));
        self::assertSame(0, $product->balanceInt);

        $transfer->refresh();
        self::assertSame(Transfer::STATUS_REFUND, $transfer->status);

        self::assertFalse($buyer->safeRefund($product));
        self::assertSame($buyer->balanceInt, (int) $product->getAmountProduct($buyer));

        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertSame($buyer->balanceInt, $product->getPersonalDiscount($buyer));
        self::assertSame(
            $product->balanceInt,
            (int) ($product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer))
        );

        self::assertSame(Transfer::STATUS_PAID, $transfer->status);

        self::assertTrue($buyer->refund($product));
        self::assertSame($buyer->balanceInt, (int) $product->getAmountProduct($buyer));
        self::assertSame(0, $product->balanceInt);

        $transfer->refresh();
        self::assertSame(Transfer::STATUS_REFUND, $transfer->status);
    }

    public function testForceRefund(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertSame($buyer->balanceInt, (int) $product->getAmountProduct($buyer));

        $transfer = $buyer->pay($product);
        self::assertSame($buyer->balanceInt, $product->getPersonalDiscount($buyer));

        self::assertSame(
            $product->balanceInt,
            (int) ($product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer))
        );

        self::assertSame((int) $transfer->discount, $product->getPersonalDiscount($buyer));

        $product->withdraw($product->balance);
        self::assertSame(0, $product->balanceInt);

        self::assertFalse($buyer->safeRefund($product));
        self::assertTrue($buyer->forceRefund($product));

        self::assertSame(
            $product->balanceInt,
            (int) -($product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer))
        );

        self::assertSame($buyer->balanceInt, (int) $product->getAmountProduct($buyer));
        $product->deposit(-$product->balance);
        $buyer->withdraw($buyer->balance);

        self::assertSame(0, $product->balanceInt);
        self::assertSame(0, $buyer->balanceInt);
    }

    public function testOutOfStock(): void
    {
        $this->expectException(ProductEnded::class);
        $this->expectExceptionCode(ExceptionInterface::PRODUCT_ENDED);
        $this->expectExceptionMessageStrict(trans('wallet::errors.product_stock'));

        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        $buyer->deposit($product->getAmountProduct($buyer));
        $buyer->pay($product);
        $buyer->pay($product);
    }

    public function testForcePay(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->forcePay($product);

        self::assertSame(
            $buyer->balanceInt,
            (int) -($product->getAmountProduct($buyer) - $product->getPersonalDiscount($buyer))
        );

        $buyer->deposit(-$buyer->balance);
        self::assertSame(0, $buyer->balanceInt);
    }

    public function testPayFree(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);

        $transfer = $buyer->payFree($product);
        self::assertSame(Transaction::TYPE_DEPOSIT, $transfer->deposit->type);
        self::assertSame(Transaction::TYPE_WITHDRAW, $transfer->withdraw->type);

        self::assertSame(0, $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);

        $buyer->refund($product);
        self::assertSame(0, $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);
    }

    public function testFreePay(): void
    {
        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        $buyer->forceWithdraw(1000);
        self::assertSame(-1000, $buyer->balanceInt);

        $transfer = $buyer->payFree($product);
        self::assertSame(Transaction::TYPE_DEPOSIT, $transfer->deposit->type);
        self::assertSame(Transaction::TYPE_WITHDRAW, $transfer->withdraw->type);

        self::assertSame(-1000, $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);

        $buyer->refund($product);
        self::assertSame(-1000, $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);
    }

    public function testPayFreeOutOfStock(): void
    {
        $this->expectException(ProductEnded::class);
        $this->expectExceptionCode(ExceptionInterface::PRODUCT_ENDED);
        $this->expectExceptionMessageStrict(trans('wallet::errors.product_stock'));

        /**
         * @var Buyer        $buyer
         * @var ItemDiscount $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemDiscountFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertNotNull($buyer->payFree($product));
        $buyer->payFree($product);
    }
}
