<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemWalletFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\Item;
use Bavix\Wallet\Test\Infra\Models\ItemWallet;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
class ProductTest extends TestCase
{
    public function testPay(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame($buyer->balanceInt, 0);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertSame($buyer->balanceInt, (int) $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertSame($transfer->status, Transfer::STATUS_PAID);

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
        self::assertInstanceOf(Item::class, $transfer->to);
        self::assertInstanceOf(Wallet::class, $transfer->to->wallet);

        self::assertSame($buyer->wallet->getKey(), $transfer->from->getKey());
        self::assertSame($buyer->getKey(), $transfer->from->holder->getKey());
        self::assertSame($product->getKey(), $transfer->to->getKey());

        self::assertSame(0, $buyer->balanceInt);
        self::assertNull($buyer->safePay($product));
    }

    public function testRefund(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertSame((int) $product->getAmountProduct($buyer), $buyer->balanceInt);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertSame(Transfer::STATUS_PAID, $transfer->status);

        self::assertTrue($buyer->refund($product));
        self::assertSame((int) $product->getAmountProduct($buyer), $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);

        $transfer->refresh();
        self::assertSame(Transfer::STATUS_REFUND, $transfer->status);

        self::assertFalse($buyer->safeRefund($product));
        self::assertSame((int) $product->getAmountProduct($buyer), $buyer->balanceInt);

        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertSame(0, $buyer->balanceInt);
        self::assertSame((int) $product->getAmountProduct($buyer), $product->balanceInt);
        self::assertSame(Transfer::STATUS_PAID, $transfer->status);

        self::assertTrue($buyer->refund($product));
        self::assertSame((int) $product->getAmountProduct($buyer), $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);

        $transfer->refresh();
        self::assertSame(Transfer::STATUS_REFUND, $transfer->status);
    }

    public function testForceRefund(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertSame($product->getAmountProduct($buyer), $buyer->balanceInt);

        $buyer->pay($product);
        self::assertSame(0, $buyer->balanceInt);
        self::assertSame($product->getAmountProduct($buyer), $product->balanceInt);

        $product->withdraw($product->balanceInt);
        self::assertSame(0, $product->balanceInt);

        self::assertFalse($buyer->safeRefund($product));
        self::assertTrue($buyer->forceRefund($product));

        self::assertSame((int) -$product->getAmountProduct($buyer), $product->balanceInt);
        self::assertSame((int) $product->getAmountProduct($buyer), $buyer->balanceInt);
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
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        $buyer->deposit($product->getAmountProduct($buyer));
        $buyer->pay($product);
        $buyer->pay($product);
    }

    public function testForcePay(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->forcePay($product);

        self::assertSame((int) -$product->getAmountProduct($buyer), $buyer->balanceInt);

        $buyer->deposit(-$buyer->balance);
        self::assertSame(0, $buyer->balanceInt);
    }

    public function testPayFree(): void
    {
        /**
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
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
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
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
         * @var Buyer $buyer
         * @var Item  $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertNotNull($buyer->payFree($product));
        $buyer->payFree($product);
    }

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/237
     *
     * @throws
     */
    public function testProductMultiWallet(): void
    {
        /**
         * @var Buyer      $buyer
         * @var ItemWallet $product
         */
        $buyer = BuyerFactory::new()->create();
        $product = ItemWalletFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($product->getAmountProduct($buyer));
        self::assertSame((string) $product->getAmountProduct($buyer), $buyer->balance);

        $product->createWallet(['name' => 'testing']);
        app(DatabaseServiceInterface::class)->transaction(function () use ($product, $buyer) {
            $transfer = $buyer->pay($product);
            $product->transfer($product->getWallet('testing'), $transfer->deposit->amount, $transfer->toArray());
        });

        self::assertSame(0, $product->balanceInt);
        self::assertSame(0, $buyer->balanceInt);
        self::assertSame((string) $product->getAmountProduct($buyer), $product->getWallet('testing')->balance);
    }
}
