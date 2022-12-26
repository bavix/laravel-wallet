<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Internal\Events\TransactionCreatedEventInterface;
use Bavix\Wallet\Internal\Events\WalletCreatedEventInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Services\PurchaseServiceInterface;
use Bavix\Wallet\Test\Infra\Exceptions\UnknownEventException;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemFactory;
use Bavix\Wallet\Test\Infra\Listeners\BalanceUpdatedThrowDateListener;
use Bavix\Wallet\Test\Infra\Listeners\BalanceUpdatedThrowIdListener;
use Bavix\Wallet\Test\Infra\Listeners\BalanceUpdatedThrowUuidListener;
use Bavix\Wallet\Test\Infra\Listeners\TransactionCreatedThrowListener;
use Bavix\Wallet\Test\Infra\Listeners\WalletCreatedThrowListener;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Services\ClockFakeService;
use Bavix\Wallet\Test\Infra\TestCase;
use DateTimeInterface;
use Illuminate\Support\Facades\Event;

/**
 * @internal
 */
final class EventTest extends TestCase
{
    public function testBalanceUpdatedThrowUuidListener(): void
    {
        Event::listen(BalanceUpdatedEventInterface::class, BalanceUpdatedThrowUuidListener::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertSame(0, $buyer->wallet->balanceInt);

        // unit
        $this->expectException(UnknownEventException::class);
        $this->expectExceptionMessage($buyer->wallet->uuid);
        $this->expectExceptionCode(123);

        $buyer->deposit(123);
    }

    public function testBalanceUpdatedThrowIdListener(): void
    {
        Event::listen(BalanceUpdatedEventInterface::class, BalanceUpdatedThrowIdListener::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertSame(0, $buyer->wallet->balanceInt); // auto create wallet

        // unit
        $this->expectException(UnknownEventException::class);
        $this->expectExceptionMessage((string) $buyer->wallet->getKey());
        $this->expectExceptionCode(456);

        $buyer->deposit(456);
    }

    public function testBalanceUpdatedThrowDateListener(): void
    {
        $this->app->bind(ClockServiceInterface::class, ClockFakeService::class);

        Event::listen(BalanceUpdatedEventInterface::class, BalanceUpdatedThrowDateListener::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertSame(0, $buyer->wallet->balanceInt);

        // unit
        $this->expectException(UnknownEventException::class);
        $this->expectExceptionCode(789);

        $buyer->deposit(789);
    }

    public function testWalletCreatedThrowListener(): void
    {
        $this->app->bind(ClockServiceInterface::class, ClockFakeService::class);

        Event::listen(WalletCreatedEventInterface::class, WalletCreatedThrowListener::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $uuidFactoryService = app(UuidFactoryServiceInterface::class);
        $buyer->wallet->uuid = $uuidFactoryService->uuid4();

        $holderType = $buyer->getMorphClass();
        $uuid = $buyer->wallet->uuid;
        $createdAt = app(ClockServiceInterface::class)->now()->format(DateTimeInterface::ATOM);

        $message = hash('sha256', $holderType . $uuid . $createdAt);

        // unit
        $this->expectException(UnknownEventException::class);
        $this->expectExceptionMessage($message);

        $buyer->getBalanceIntAttribute();
    }

    /**
     * @throws ExceptionInterface
     */
    public function testBalanceNotChanged(): void
    {
        Event::listen(BalanceUpdatedEventInterface::class, BalanceUpdatedThrowIdListener::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertSame(0, $buyer->wallet->balanceInt);

        self::assertNotNull($buyer->deposit(0)); // no event
        self::assertNotNull($buyer->withdraw(0)); // no event

        app(DatabaseServiceInterface::class)->transaction(function () use ($buyer) {
            $transaction = $buyer->deposit(100);
            self::assertNotNull($transaction);
            self::assertSame(100, $transaction->amountInt);

            $transaction = $buyer->withdraw(100);
            self::assertNotNull($transaction);
            self::assertSame(-100, $transaction->amountInt);
        }); // no event

        /**
         * The balance has not changed. Balance update event will not be generated.
         */
        self::assertSame(0, $buyer->balanceInt);
        self::assertCount(4, $buyer->transactions()->get());
    }

    /**
     * @throws ExceptionInterface
     */
    public function testTransactionCreatedThrowListener(): void
    {
        $this->app->bind(ClockServiceInterface::class, ClockFakeService::class);

        Event::listen(TransactionCreatedEventInterface::class, TransactionCreatedThrowListener::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertSame(0, $buyer->wallet->balanceInt);

        $createdAt = app(ClockServiceInterface::class)
            ->now()
            ->format(DateTimeInterface::ATOM)
        ;

        $message = hash('sha256', Transaction::TYPE_DEPOSIT . $buyer->wallet->getKey() . $createdAt);

        // unit
        $this->expectException(UnknownEventException::class);
        $this->expectExceptionMessage($message);

        $buyer->deposit(100);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testTransactionCreatedMultiListener(): void
    {
        /** @var array<array<int, int>> $transactionIds */
        /** @var array<array<int, int>> $transactionCounts */
        $transactionIds = [];
        $transactionCounts = [];
        Event::listen(
            TransactionCreatedEventInterface::class,
            static function (TransactionCreatedEventInterface $event) use (
                &$transactionIds,
                &$transactionCounts
            ): void {
                $transactionCounts[$event->getWalletId()] = ($transactionCounts[$event->getWalletId()] ?? 0) + 1;
                $transactionIds[$event->getWalletId()][] = $event->getId();
            },
        );

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertSame(0, $buyer->wallet->balanceInt);

        $products = ItemFactory::times(10)->create([
            'quantity' => 1,
        ]);

        $cart = app(Cart::class)->withItems($products);
        foreach ($cart->getItems() as $product) {
            self::assertSame(0, $product->getBalanceIntAttribute());
        }

        self::assertSame($buyer->balance, $buyer->wallet->balance);

        $depositTransaction = $buyer->deposit($cart->getTotal($buyer));
        self::assertNotNull($depositTransaction); // +1

        self::assertSame($buyer->balance, $buyer->wallet->balance);

        $transfers = $buyer->payCart($cart); // +10
        self::assertCount(count($cart), $transfers);
        self::assertTrue((bool) app(PurchaseServiceInterface::class)->already($buyer, $cart->getBasketDto()));
        self::assertSame(0, $buyer->balanceInt);

        $resultIds = [$depositTransaction->getKey()];
        $valueIds = $transactionIds[$buyer->wallet->getKey()] ?? [];
        foreach ($transfers as $transfer) {
            $resultIds[] = $transfer->withdraw->getKey();
            self::assertSame(1, $transactionCounts[$transfer->deposit->wallet_id] ?? 0);
        }

        sort($valueIds);
        sort($resultIds);

        self::assertSame(1 + 20, array_sum($transactionCounts)); // deposit+withdraw
        self::assertSame(1 + 10, $transactionCounts[$buyer->wallet->getKey()] ?? 0);

        self::assertCount(1 + 10, $resultIds);
        self::assertCount(1 + 10, $valueIds);

        self::assertSame($valueIds, $resultIds);
    }
}
