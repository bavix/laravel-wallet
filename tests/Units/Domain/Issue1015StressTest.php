<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Events\BalanceCommittingEventInterface;
use Bavix\Wallet\Internal\Events\TransactionCommittingEventInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Bavix\Wallet\Services\TransactionServiceInterface;
use Bavix\Wallet\Test\Infra\PackageModels\TransactionState;
use Bavix\Wallet\Test\Infra\PackageModels\Transfer;
use Bavix\Wallet\Test\Infra\ProjectionTestCase;
use Bavix\Wallet\Test\Infra\Services\ProjectedTransactionService;
use Bavix\Wallet\Test\Infra\Transform\TransactionDtoTransformerStateProjection;
use Illuminate\Support\Facades\Event;
use Override;

/**
 * @internal
 */
final class Issue1015StressTest extends ProjectionTestCase
{
    use StressTestSetupTrait;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->app?->singleton(
            TransactionDtoTransformerInterface::class,
            TransactionDtoTransformerStateProjection::class
        );
        $this->app?->singleton(TransactionServiceInterface::class, ProjectedTransactionService::class);

        Event::forget(BalanceCommittingEventInterface::class);
        Event::forget(TransactionCommittingEventInterface::class);
    }

    public function testIssue1015TransactionStateOnMultipleWalletsAndProducts(): void
    {
        $math = app(MathServiceInterface::class);
        $transactionIds = [];
        $expectedFinalBalances = [];
        $walletExpectedBalances = [];

        for ($walletIndex = 0; $walletIndex < 10; $walletIndex++) {
            $buyer = $this->createBuyerWithPaymentWallet($walletIndex);
            $payment = $buyer->wallet;
            $cart = $this->createCartWithProductsAndReceivingWallets($walletIndex, $buyer);
            $productsCount = $this->getProductsCountForWallet($walletIndex);

            /** @var TransactionState $warmupDeposit */
            $warmupDeposit = $payment->deposit(1000);
            $transactionIds[] = $warmupDeposit->getKey();

            $warmupDepositWalletId = $warmupDeposit->wallet_id;
            $warmupDepositPrevious = $walletExpectedBalances[$warmupDepositWalletId] ?? '0';
            $warmupDepositExpected = $math->round($math->add($warmupDepositPrevious, $warmupDeposit->amount));
            $expectedFinalBalances[$warmupDeposit->getKey()] = $warmupDepositExpected;
            $walletExpectedBalances[$warmupDepositWalletId] = $warmupDepositExpected;

            /** @var TransactionState $warmupWithdraw */
            $warmupWithdraw = $payment->withdraw(100);
            $transactionIds[] = $warmupWithdraw->getKey();

            $warmupWithdrawWalletId = $warmupWithdraw->wallet_id;
            $warmupWithdrawPrevious = $walletExpectedBalances[$warmupWithdrawWalletId] ?? '0';
            $warmupWithdrawExpected = $math->round($math->add($warmupWithdrawPrevious, $warmupWithdraw->amount));
            $expectedFinalBalances[$warmupWithdraw->getKey()] = $warmupWithdrawExpected;
            $walletExpectedBalances[$warmupWithdrawWalletId] = $warmupWithdrawExpected;

            $fundingTransaction = $payment->deposit($cart->getTotal($buyer));
            $transactionIds[] = $fundingTransaction->getKey();

            $fundingWalletId = $fundingTransaction->wallet_id;
            $fundingPrevious = $walletExpectedBalances[$fundingWalletId] ?? '0';
            $fundingExpected = $math->round($math->add($fundingPrevious, $fundingTransaction->amount));
            $expectedFinalBalances[$fundingTransaction->getKey()] = $fundingExpected;
            $walletExpectedBalances[$fundingWalletId] = $fundingExpected;

            $transfers = $payment->payCart($cart);
            self::assertCount($productsCount, $transfers);

            foreach ($transfers as $transfer) {
                self::assertInstanceOf(Transfer::class, $transfer);
                $transactionIds[] = $transfer->deposit_id;
                $transactionIds[] = $transfer->withdraw_id;

                $withdraw = $transfer->withdraw;
                self::assertInstanceOf(TransactionState::class, $withdraw);
                $withdrawWalletId = $withdraw->wallet_id;
                $withdrawPrevious = $walletExpectedBalances[$withdrawWalletId] ?? '0';
                $withdrawExpected = $math->round($math->add($withdrawPrevious, $withdraw->amount));
                $expectedFinalBalances[$withdraw->getKey()] = $withdrawExpected;
                $walletExpectedBalances[$withdrawWalletId] = $withdrawExpected;

                $deposit = $transfer->deposit;
                self::assertInstanceOf(TransactionState::class, $deposit);
                $depositWalletId = $deposit->wallet_id;
                $depositPrevious = $walletExpectedBalances[$depositWalletId] ?? '0';
                $depositExpected = $math->round($math->add($depositPrevious, $deposit->amount));
                $expectedFinalBalances[$deposit->getKey()] = $depositExpected;
                $walletExpectedBalances[$depositWalletId] = $depositExpected;
            }

            $fundingTransaction = $payment->deposit($cart->getTotal($buyer));
            $transactionIds[] = $fundingTransaction->getKey();

            $fundingWalletId = $fundingTransaction->wallet_id;
            $fundingPrevious = $walletExpectedBalances[$fundingWalletId] ?? '0';
            $fundingExpected = $math->round($math->add($fundingPrevious, $fundingTransaction->amount));
            $expectedFinalBalances[$fundingTransaction->getKey()] = $fundingExpected;
            $walletExpectedBalances[$fundingWalletId] = $fundingExpected;

            $transfers = $payment->payCart($cart);
            self::assertCount($productsCount, $transfers);

            foreach ($transfers as $transfer) {
                self::assertInstanceOf(Transfer::class, $transfer);
                $transactionIds[] = $transfer->deposit_id;
                $transactionIds[] = $transfer->withdraw_id;

                $withdraw = $transfer->withdraw;
                self::assertInstanceOf(TransactionState::class, $withdraw);
                $withdrawWalletId = $withdraw->wallet_id;
                $withdrawPrevious = $walletExpectedBalances[$withdrawWalletId] ?? '0';
                $withdrawExpected = $math->round($math->add($withdrawPrevious, $withdraw->amount));
                $expectedFinalBalances[$withdraw->getKey()] = $withdrawExpected;
                $walletExpectedBalances[$withdrawWalletId] = $withdrawExpected;

                $deposit = $transfer->deposit;
                self::assertInstanceOf(TransactionState::class, $deposit);
                $depositWalletId = $deposit->wallet_id;
                $depositPrevious = $walletExpectedBalances[$depositWalletId] ?? '0';
                $depositExpected = $math->round($math->add($depositPrevious, $deposit->amount));
                $expectedFinalBalances[$deposit->getKey()] = $depositExpected;
                $walletExpectedBalances[$depositWalletId] = $depositExpected;
            }
        }

        $transactionIds = array_values(array_unique($transactionIds));
        sort($transactionIds);

        /** @var list<TransactionState> $transactions */
        $transactions = TransactionState::query()
            ->whereIn('id', $transactionIds)
            ->orderBy('id')
            ->get()
            ->all();

        self::assertCount(count($transactionIds), $transactions);

        $walletRunningBalances = [];

        foreach ($transactions as $transaction) {
            self::assertNotNull($transaction->balance_after);
            self::assertNotNull($transaction->state_hash);

            $expectedFinalBalance = $expectedFinalBalances[$transaction->getKey()] ?? null;
            self::assertIsString($expectedFinalBalance);
            self::assertSame($expectedFinalBalance, $transaction->balance_after);

            self::assertSame(
                hash('sha256', $transaction->uuid.':'.$transaction->amount.':'.$expectedFinalBalance),
                $transaction->state_hash
            );

            $walletId = $transaction->wallet_id;
            $previousBalance = $walletRunningBalances[$walletId] ?? '0';
            $expectedBalance = $math->round($math->add($previousBalance, $transaction->amount));

            self::assertSame($expectedBalance, $transaction->balance_after);
            $walletRunningBalances[$walletId] = $expectedBalance;
        }
    }
}
