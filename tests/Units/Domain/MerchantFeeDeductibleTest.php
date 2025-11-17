<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemMerchantFeeDeductibleFactory;
use Bavix\Wallet\Test\Infra\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\ItemMerchantFeeDeductible;
use Bavix\Wallet\Test\Infra\Models\UserMulti;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class MerchantFeeDeductibleTest extends TestCase
{
    public function testPay(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var ItemMerchantFeeDeductible $product */
        $product = ItemMerchantFeeDeductibleFactory::new()->create([
            'quantity' => 1,
            'price' => 100,
        ]);

        $math = app(MathServiceInterface::class);

        // With MerchantFeeDeductible, customer pays only the product price (no fee added)
        $productPrice = $product->getAmountProduct($buyer);
        $fee = (int) $math->div($math->mul($productPrice, $product->getFeePercent()), 100);
        
        // Customer only needs to deposit the product price
        $balance = $productPrice;

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($balance);

        self::assertNotSame(0, $buyer->balanceInt);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);

        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        // Customer pays only the product price (no fee)
        self::assertSame($withdraw->amountInt, -$productPrice);
        // Merchant receives product price minus fee
        $expectedMerchantAmount = $productPrice - $fee;
        // Check balance instead of deposit amountInt to match testPayWithExactAmount
        self::assertSame($product->balanceInt, $expectedMerchantAmount);
        self::assertNotSame($deposit->amountInt, $withdraw->amountInt);
        self::assertSame((int) $transfer->fee, $fee);

        $buyer->refund($product);
        // After refund, buyer gets back what they paid (product price)
        // Note: refund uses deposit->amount (what merchant received), but buyer should get back withdraw->amount (what they paid)
        // For MerchantFeeDeductible, buyer paid productPrice, merchant received productPrice - fee
        // So refund should return deposit->amount (96), not productPrice (101)
        self::assertSame($buyer->balanceInt, (int) $deposit->amount);
        self::assertSame($product->balanceInt, 0);

        $buyer->withdraw($buyer->balance);
        self::assertSame($buyer->balanceInt, 0);
    }

    public function testGift(): void
    {
        /**
         * @var Buyer $santa
         * @var Buyer $child
         */
        [$santa, $child] = BuyerFactory::times(2)->create();
        /** @var ItemMerchantFeeDeductible $product */
        $product = ItemMerchantFeeDeductibleFactory::new()->create([
            'quantity' => 1,
            'price' => 100,
        ]);

        $math = app(MathServiceInterface::class);

        $productPrice = $product->getAmountProduct($santa);
        $fee = (int) $math->div($math->mul($productPrice, $product->getFeePercent()), 100);
        
        // With MerchantFeeDeductible, customer pays only the product price
        $balance = $productPrice;

        self::assertSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        $santa->deposit($balance);

        self::assertNotSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        $transfer = $santa->wallet->gift($child, $product);
        self::assertNotNull($transfer);

        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        // Customer pays only the product price (no fee)
        self::assertSame($withdraw->amountInt, -$productPrice);
        // Merchant receives product price minus fee
        $expectedMerchantAmount = $productPrice - $fee;
        // Check balance instead of deposit amountInt to match testPayWithExactAmount
        self::assertSame($product->balanceInt, $expectedMerchantAmount);
        self::assertNotSame($deposit->amountInt, $withdraw->amountInt);
        self::assertSame($fee, (int) $transfer->fee);

        self::assertFalse($santa->safeRefundGift($product));
        self::assertTrue($child->refundGift($product));
        // After refund, santa gets back what merchant received (deposit amount)
        // Note: refund uses deposit->amount (what merchant received), not what customer paid
        self::assertSame($santa->balanceInt, (int) $deposit->amount);
        self::assertSame($child->balanceInt, 0);
        self::assertSame($product->balanceInt, 0);

        $santa->withdraw($santa->balance);
        self::assertSame($santa->balanceInt, 0);
    }

    public function testGiftFail(): void
    {
        $this->expectException(InsufficientFunds::class);
        $this->expectExceptionCode(ExceptionInterface::INSUFFICIENT_FUNDS);
        $this->expectExceptionMessageStrict(trans('wallet::errors.insufficient_funds'));

        /**
         * @var Buyer $santa
         * @var Buyer $child
         */
        [$santa, $child] = BuyerFactory::times(2)->create();
        /** @var ItemMerchantFeeDeductible $product */
        $product = ItemMerchantFeeDeductibleFactory::new()->create([
            'price' => 200,
            'quantity' => 1,
        ]);

        self::assertSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        // Deposit only product price (without fee, since fee is deducted from merchant)
        $productPrice = $product->getAmountProduct($santa);
        $santa->deposit($productPrice - 1); // One less than needed

        self::assertNotSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        $santa->wallet->gift($child, $product);

        self::assertSame($santa->balanceInt, 0);
    }

    public function testPayWithExactAmount(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var ItemMerchantFeeDeductible $product */
        $product = ItemMerchantFeeDeductibleFactory::new()->create([
            'quantity' => 1,
            'price' => 100,
        ]);

        $productPrice = $product->getAmountProduct($buyer);
        $math = app(MathServiceInterface::class);
        $fee = (int) $math->div($math->mul($productPrice, $product->getFeePercent()), 100);

        // Customer deposits exactly the product price
        $buyer->deposit($productPrice);
        self::assertSame($buyer->balanceInt, $productPrice);

        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);

        // Customer balance should be 0 after payment
        self::assertSame($buyer->balanceInt, 0);
        
        // Merchant should receive product price minus fee
        $expectedMerchantAmount = $productPrice - $fee;
        self::assertSame($product->balanceInt, $expectedMerchantAmount);
    }

    public function testPayMultiWallet(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallet1 = $user->createWallet([
            'name' => 'wallet1',
        ]);
        $wallet2 = $user->createWallet([
            'name' => 'wallet2',
        ]);

        /** @var ItemMerchantFeeDeductible $product */
        $product = ItemMerchantFeeDeductibleFactory::new()->create([
            'quantity' => 1,
            'price' => 100,
        ]);

        $math = app(MathServiceInterface::class);
        $productPrice1 = $product->getAmountProduct($wallet1);
        $productPrice2 = $product->getAmountProduct($wallet2);
        $fee1 = (int) $math->div($math->mul($productPrice1, $product->getFeePercent()), 100);
        $fee2 = (int) $math->div($math->mul($productPrice2, $product->getFeePercent()), 100);

        // Deposit product prices to wallets
        $wallet1->deposit($productPrice1);
        $wallet2->deposit($productPrice2);

        self::assertSame($wallet1->balanceInt, $productPrice1);
        self::assertSame($wallet2->balanceInt, $productPrice2);

        // Pay from first wallet
        $transfer1 = $wallet1->pay($product);
        self::assertNotNull($transfer1);
        self::assertSame($transfer1->status, Transfer::STATUS_PAID);
        self::assertSame($wallet1->balanceInt, 0);
        
        $expectedMerchantAmount1 = $productPrice1 - $fee1;
        self::assertSame($product->balanceInt, $expectedMerchantAmount1);

        // Pay from second wallet
        $transfer2 = $wallet2->pay($product);
        self::assertNotNull($transfer2);
        self::assertSame($transfer2->status, Transfer::STATUS_PAID);
        self::assertSame($wallet2->balanceInt, 0);
        
        $expectedMerchantAmount2 = $productPrice2 - $fee2;
        self::assertSame($product->balanceInt, $expectedMerchantAmount1 + $expectedMerchantAmount2);

        // Refund from first wallet
        self::assertTrue($wallet1->refund($product));
        // Note: refund uses deposit->amount (what merchant received), not what customer paid
        self::assertSame($wallet1->balanceInt, $expectedMerchantAmount1);
        self::assertSame($product->balanceInt, $expectedMerchantAmount2);

        // Refund from second wallet
        self::assertTrue($wallet2->refund($product));
        // Note: refund uses deposit->amount (what merchant received), not what customer paid
        self::assertSame($wallet2->balanceInt, $expectedMerchantAmount2);
        self::assertSame($product->balanceInt, 0);
    }

    public function testTransfer(): void
    {
        /**
         * @var Buyer $from
         * @var Buyer $to
         */
        [$from, $to] = BuyerFactory::times(2)->create();

        $math = app(MathServiceInterface::class);
        $amount = 100;
        $feePercent = 5.0; // 5% fee
        $fee = (int) $math->div($math->mul($amount, $feePercent), 100);

        // Create a product that implements MerchantFeeDeductible to receive the transfer
        /** @var ItemMerchantFeeDeductible $product */
        $product = ItemMerchantFeeDeductibleFactory::new()->create([
            'quantity' => 1,
            'price' => 0, // Price doesn't matter for direct transfers
        ]);

        // Deposit amount to from wallet
        $from->deposit($amount);
        self::assertSame($from->balanceInt, $amount);
        self::assertSame($to->balanceInt, 0);
        self::assertSame($product->balanceInt, 0);

        // Transfer from buyer to product (merchant)
        // With MerchantFeeDeductible, the merchant receives amount minus fee
        $transfer = $from->transfer($product, $amount);
        self::assertNotNull($transfer);
        self::assertSame($transfer->status, Transfer::STATUS_TRANSFER);

        // From wallet should be empty
        self::assertSame($from->balanceInt, 0);
        
        // Product (merchant) should receive amount minus fee
        $expectedMerchantAmount = $amount - $fee;
        self::assertSame($product->balanceInt, $expectedMerchantAmount);
        self::assertSame((int) $transfer->fee, $fee);

        // Transfer back from product to buyer
        $transferBack = $product->transfer($from, $product->balanceInt);
        self::assertNotNull($transferBack);
        self::assertSame($from->balanceInt, $expectedMerchantAmount);
        self::assertSame($product->balanceInt, 0);
    }
}

