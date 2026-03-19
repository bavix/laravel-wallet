<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Events\BalanceCommittingEventInterface;
use Bavix\Wallet\Internal\Events\TransactionCommittingEventInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemFactory;
use Bavix\Wallet\Test\Infra\Listeners\TransactionStateProjectorListener;
use Bavix\Wallet\Test\Infra\Listeners\WalletStateProjectorListener;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\Item;
use Bavix\Wallet\Test\Infra\PackageModels\TransactionState;
use Bavix\Wallet\Test\Infra\PackageModels\Transfer;
use Bavix\Wallet\Test\Infra\PackageModels\WalletState;
use Bavix\Wallet\Test\Infra\ProjectionTestCase;
use Illuminate\Support\Facades\Event;

/**
 * @internal
 */
final class StateProjectionExtensionTest extends ProjectionTestCase
{
    public function testWalletStateProjectionViaBalanceCommittingEvent(): void
    {
        $this->enableWalletStateProjection();

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $buyer->deposit(150);

        $this->assertWalletState($buyer->wallet, '150', '0');

        $buyer->withdraw(50);

        $this->assertWalletState($buyer->wallet, '100', '0');
    }

    public function testWalletStateProjectionPreservesHeldBalance(): void
    {
        $this->enableWalletStateProjection();

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $wallet = $buyer->wallet;
        $wallet->forceFill([
            'held_balance' => '25',
        ])->saveQuietly();

        $buyer->deposit(75);
        $this->assertWalletState($wallet, '75', '25');
    }

    public function testWalletStateProjectionUpdatesBothSidesOnTransfer(): void
    {
        $this->enableWalletStateProjection();

        /** @var Buyer $from */
        $from = BuyerFactory::new()->create();
        /** @var Buyer $to */
        $to = BuyerFactory::new()->create();

        $from->deposit(200);
        $from->transfer($to, 70);

        $this->assertWalletState($from->wallet, '130', '0');
        $this->assertWalletState($to->wallet, '70', '0');
    }

    public function testWalletStateHashChangesWhenBalanceChanges(): void
    {
        $this->enableWalletStateProjection();

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $buyer->deposit(50);
        /** @var WalletState $wallet */
        $wallet = WalletState::query()->findOrFail($buyer->wallet->getKey());
        $stateHashAfterDeposit = $wallet->state_hash;

        $buyer->deposit(20);

        /** @var WalletState $wallet */
        $wallet = WalletState::query()->findOrFail($buyer->wallet->getKey());
        self::assertNotNull($stateHashAfterDeposit);
        self::assertNotSame($stateHashAfterDeposit, $wallet->state_hash);
        $this->assertWalletState($wallet, '70', '0');
    }

    public function testTransactionStateProjectionViaTransactionCommittingEvent(): void
    {
        Event::listen(TransactionCommittingEventInterface::class, TransactionStateProjectorListener::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        /** @var TransactionState $deposit */
        $deposit = $buyer->deposit(100);
        /** @var TransactionState $withdraw */
        $withdraw = $buyer->withdraw(30);

        /** @var TransactionState $deposit */
        $deposit = TransactionState::query()->findOrFail($deposit->getKey());
        /** @var TransactionState $withdraw */
        $withdraw = TransactionState::query()->findOrFail($withdraw->getKey());

        self::assertSame('100', $deposit->balance_after);
        self::assertSame(hash('sha256', $deposit->id.':'.$deposit->amount.':100'), $deposit->state_hash);

        self::assertSame('70', $withdraw->balance_after);
        self::assertSame(hash('sha256', $withdraw->id.':'.$withdraw->amount.':70'), $withdraw->state_hash);
    }

    public function testIssue1015ExtensionViaCommittingEvents(): void
    {
        Event::listen(BalanceCommittingEventInterface::class, WalletStateProjectorListener::class);
        Event::listen(TransactionCommittingEventInterface::class, TransactionStateProjectorListener::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $buyer->wallet->forceFill([
            'held_balance' => '30',
        ])->saveQuietly();

        /** @var TransactionState $deposit */
        $deposit = $buyer->deposit(120);
        /** @var TransactionState $withdraw */
        $withdraw = $buyer->withdraw(20);

        /** @var WalletState $wallet */
        $wallet = WalletState::query()->findOrFail($buyer->wallet->getKey());
        /** @var TransactionState $deposit */
        $deposit = TransactionState::query()->findOrFail($deposit->getKey());
        /** @var TransactionState $withdraw */
        $withdraw = TransactionState::query()->findOrFail($withdraw->getKey());

        self::assertSame('100', $wallet->balance_after);
        self::assertSame('30', $wallet->held_balance);
        self::assertSame(hash('sha256', $wallet->uuid.':100:30'), $wallet->state_hash);

        self::assertSame('120', $deposit->balance_after);
        self::assertSame(hash('sha256', $deposit->id.':'.$deposit->amount.':120'), $deposit->state_hash);
        self::assertSame('100', $withdraw->balance_after);
        self::assertSame(hash('sha256', $withdraw->id.':'.$withdraw->amount.':100'), $withdraw->state_hash);
    }

    public function testIssue1015TransactionProjectionWorksForCartBatch(): void
    {
        Event::listen(TransactionCommittingEventInterface::class, TransactionStateProjectorListener::class);

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

        /** @var array<int, TransactionState> $transactions */
        $transactions = TransactionState::query()
            ->whereIn('id', $transactionIds)
            ->get()
            ->all();

        self::assertCount(4, $transactions);

        foreach ($transactions as $transaction) {
            self::assertNotNull($transaction->balance_after);
            self::assertNotNull($transaction->state_hash);
            self::assertSame(
                hash('sha256', $transaction->id.':'.$transaction->amount.':'.$transaction->balance_after),
                $transaction->state_hash
            );
        }
    }

    private function enableWalletStateProjection(): void
    {
        Event::listen(BalanceCommittingEventInterface::class, WalletStateProjectorListener::class);
    }

    private function assertWalletState(WalletModel $wallet, string $balanceAfter, string $heldBalance): void
    {
        /** @var WalletState $freshWallet */
        $freshWallet = WalletState::query()->findOrFail($wallet->getKey());
        self::assertSame($balanceAfter, $freshWallet->balance_after);
        self::assertSame($heldBalance, $freshWallet->held_balance);
        self::assertSame(
            hash('sha256', $freshWallet->uuid.':'.$balanceAfter.':'.$heldBalance),
            $freshWallet->state_hash
        );
    }
}
