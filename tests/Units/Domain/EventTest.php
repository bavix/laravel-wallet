<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Internal\Exceptions\UnknownEventException;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Listeners\BalanceUpdatedThrowDateListener;
use Bavix\Wallet\Test\Infra\Listeners\BalanceUpdatedThrowIdListener;
use Bavix\Wallet\Test\Infra\Listeners\BalanceUpdatedThrowUuidListener;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Services\ClockFakeService;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Support\Facades\Event;

/**
 * @internal
 */
class EventTest extends TestCase
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
        self::assertSame(0, $buyer->wallet->balanceInt);

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
        $this->expectExceptionMessage(ClockFakeService::FAKE_DATETIME);
        $this->expectExceptionCode(789);

        $buyer->deposit(789);
    }
}
