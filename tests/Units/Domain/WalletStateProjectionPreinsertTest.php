<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Events\BalanceCommittingEventInterface;
use Bavix\Wallet\Internal\Events\TransactionCommittingEventInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Services\TransactionServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\Item;
use Bavix\Wallet\Test\Infra\PackageModels\Transaction;
use Bavix\Wallet\Test\Infra\PackageModels\TransactionState;
use Bavix\Wallet\Test\Infra\PackageModels\Transfer;
use Bavix\Wallet\Test\Infra\Services\ProjectedTransactionService;
use Bavix\Wallet\Test\Infra\TestCase;
use Bavix\Wallet\Test\Infra\Transform\TransactionDtoTransformerStateProjection;
use Illuminate\Support\Facades\Event;
use Override;

/**
 * @internal
 */
final class WalletStateProjectionPreinsertTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        config()
            ->set('wallet.transaction.model', TransactionState::class);
        $this->app?->bind(\Bavix\Wallet\Models\Transaction::class, TransactionState::class);

        $this->app?->singleton(
            TransactionDtoTransformerInterface::class,
            TransactionDtoTransformerStateProjection::class
        );

        $this->app?->singleton(TransactionServiceInterface::class, ProjectedTransactionService::class);

        Event::forget(BalanceCommittingEventInterface::class);
        Event::forget(TransactionCommittingEventInterface::class);
    }

    public function testPreinsertProjectionForDepositAndWithdraw(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        /** @var Transaction $deposit */
        $deposit = $buyer->deposit(120);
        /** @var Transaction $withdraw */
        $withdraw = $buyer->withdraw(20);

        /** @var Transaction $deposit */
        $deposit = Transaction::query()->findOrFail($deposit->getKey());
        /** @var Transaction $withdraw */
        $withdraw = Transaction::query()->findOrFail($withdraw->getKey());

        self::assertSame('120', $deposit->final_balance);
        self::assertSame(hash('sha256', $deposit->uuid.':'.$deposit->amount.':120'), $deposit->checksum);

        self::assertSame('100', $withdraw->final_balance);
        self::assertSame(hash('sha256', $withdraw->uuid.':'.$withdraw->amount.':100'), $withdraw->checksum);
    }

    public function testPreinsertProjectionForCartBatch(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $firstProduct */
        $firstProduct = ItemFactory::new()->create([
            'quantity' => 1,
            'price' => 100,
        ]);
        /** @var Item $secondProduct */
        $secondProduct = ItemFactory::new()->create([
            'quantity' => 1,
            'price' => 200,
        ]);

        $cart = app(Cart::class)
            ->withItem($firstProduct)
            ->withItem($secondProduct);

        $buyer->deposit($cart->getTotal($buyer));
        $transfers = $buyer->payCart($cart);
        self::assertCount(2, $transfers);

        $transactionIds = [];
        foreach ($transfers as $transfer) {
            self::assertInstanceOf(Transfer::class, $transfer);
            $transactionIds[] = $transfer->deposit_id;
            $transactionIds[] = $transfer->withdraw_id;
        }

        /** @var array<int, Transaction> $transactions */
        $transactions = Transaction::query()
            ->whereIn('id', $transactionIds)
            ->get()
            ->all();

        self::assertCount(4, $transactions);

        foreach ($transactions as $transaction) {
            self::assertNotNull($transaction->final_balance);
            self::assertNotNull($transaction->checksum);
            self::assertSame(
                hash('sha256', $transaction->uuid.':'.$transaction->amount.':'.$transaction->final_balance),
                $transaction->checksum
            );
        }
    }
}
