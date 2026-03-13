<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Events\BalanceCommittingEventInterface;
use Bavix\Wallet\Internal\Events\TransactionCommittingEventInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemFactory;
use Bavix\Wallet\Test\Infra\Listeners\TransactionStateProjectorListener;
use Bavix\Wallet\Test\Infra\Listeners\WalletStateProjectorListener;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\Item;
use Bavix\Wallet\Test\Infra\PackageModels\Transaction;
use Bavix\Wallet\Test\Infra\PackageModels\TransactionMoney;
use Bavix\Wallet\Test\Infra\PackageModels\Transfer;
use Bavix\Wallet\Test\Infra\PackageModels\Wallet;
use Bavix\Wallet\Test\Infra\TestCase;
use Bavix\Wallet\Test\Infra\Transform\TransactionDtoTransformerCustom;
use Illuminate\Support\Facades\Event;
use Override;

/**
 * @internal
 */
final class WalletExtensionTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->app?->bind(TransactionDtoTransformerInterface::class, TransactionDtoTransformerCustom::class);
    }

    public function testCustomAttribute(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $transaction = $buyer->deposit(1000, [
            'bank_method' => 'VietComBank',
        ]);

        self::assertTrue($transaction->getKey() > 0);
        self::assertSame($transaction->amountInt, $buyer->balanceInt);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertSame('VietComBank', $transaction->bank_method);
    }

    public function testTransactionMoneyAttribute(): void
    {
        $this->app?->bind(\Bavix\Wallet\Models\Transaction::class, TransactionMoney::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        /** @var TransactionMoney $transaction */
        $transaction = $buyer->deposit(1000, [
            'currency' => 'EUR',
        ]);

        self::assertTrue($transaction->getKey() > 0);
        self::assertSame($transaction->amountInt, $buyer->balanceInt);
        self::assertInstanceOf(TransactionMoney::class, $transaction);
        self::assertSame('1000', $transaction->currency->amount);
        self::assertSame('EUR', $transaction->currency->currency);
    }

    public function testNoCustomAttribute(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $transaction = $buyer->deposit(1000);

        self::assertSame($transaction->amountInt, $buyer->balanceInt);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertNull($transaction->bank_method);
    }

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

    public function testWalletStateProjectionPreservesFrozenBalance(): void
    {
        $this->enableWalletStateProjection();

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $wallet = $buyer->wallet;
        $wallet->forceFill([
            'frozen_balance' => '25',
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

    public function testWalletStateChecksumChangesWhenBalanceChanges(): void
    {
        $this->enableWalletStateProjection();

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $buyer->deposit(50);
        /** @var Wallet $wallet */
        $wallet = Wallet::query()->findOrFail($buyer->wallet->getKey());
        $checksumAfterDeposit = $wallet->checksum;

        $buyer->deposit(20);

        /** @var Wallet $wallet */
        $wallet = Wallet::query()->findOrFail($buyer->wallet->getKey());
        self::assertNotNull($checksumAfterDeposit);
        self::assertNotSame($checksumAfterDeposit, $wallet->checksum);
        $this->assertWalletState($wallet, '70', '0');
    }

    public function testTransactionStateProjectionViaTransactionCommittingEvent(): void
    {
        Event::listen(TransactionCommittingEventInterface::class, TransactionStateProjectorListener::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        /** @var Transaction $deposit */
        $deposit = $buyer->deposit(100);
        /** @var Transaction $withdraw */
        $withdraw = $buyer->withdraw(30);

        /** @var Transaction $deposit */
        $deposit = Transaction::query()->findOrFail($deposit->getKey());
        /** @var Transaction $withdraw */
        $withdraw = Transaction::query()->findOrFail($withdraw->getKey());

        self::assertSame('100', $deposit->final_balance);
        self::assertSame(hash('sha256', $deposit->id.':'.$deposit->amount.':100'), $deposit->checksum);

        self::assertSame('70', $withdraw->final_balance);
        self::assertSame(hash('sha256', $withdraw->id.':'.$withdraw->amount.':70'), $withdraw->checksum);
    }

    public function testIssue1015ExtensionViaCommittingEvents(): void
    {
        Event::listen(BalanceCommittingEventInterface::class, WalletStateProjectorListener::class);
        Event::listen(TransactionCommittingEventInterface::class, TransactionStateProjectorListener::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $buyer->wallet->forceFill([
            'frozen_balance' => '30',
        ])->saveQuietly();

        /** @var Transaction $deposit */
        $deposit = $buyer->deposit(120);
        /** @var Transaction $withdraw */
        $withdraw = $buyer->withdraw(20);

        /** @var Wallet $wallet */
        $wallet = Wallet::query()->findOrFail($buyer->wallet->getKey());
        /** @var Transaction $deposit */
        $deposit = Transaction::query()->findOrFail($deposit->getKey());
        /** @var Transaction $withdraw */
        $withdraw = Transaction::query()->findOrFail($withdraw->getKey());

        self::assertSame('100', $wallet->final_balance);
        self::assertSame('30', $wallet->frozen_balance);
        self::assertSame(hash('sha256', $wallet->uuid.':100:30'), $wallet->checksum);

        self::assertSame('120', $deposit->final_balance);
        self::assertSame(hash('sha256', $deposit->id.':'.$deposit->amount.':120'), $deposit->checksum);
        self::assertSame('100', $withdraw->final_balance);
        self::assertSame(hash('sha256', $withdraw->id.':'.$withdraw->amount.':100'), $withdraw->checksum);
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
                hash('sha256', $transaction->id.':'.$transaction->amount.':'.$transaction->final_balance),
                $transaction->checksum
            );
        }
    }

    private function enableWalletStateProjection(): void
    {
        Event::listen(BalanceCommittingEventInterface::class, WalletStateProjectorListener::class);
    }

    private function assertWalletState(WalletModel $wallet, string $finalBalance, string $frozenBalance): void
    {
        /** @var Wallet $freshWallet */
        $freshWallet = Wallet::query()->findOrFail($wallet->getKey());
        self::assertSame($finalBalance, $freshWallet->final_balance);
        self::assertSame($frozenBalance, $freshWallet->frozen_balance);
        self::assertSame(
            hash('sha256', $freshWallet->uuid.':'.$finalBalance.':'.$frozenBalance),
            $freshWallet->checksum
        );
    }
}
