<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\ExchangeServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Infra\Models\UserMulti;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Support\Str;

/**
 * @internal
 */
final class ExchangeTest extends TestCase
{
    public function testSimple(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet([
            'name' => 'My USD',
            'slug' => 'usd',
        ]);

        $rub = $user->createWallet([
            'name' => 'Мои рубли',
            'slug' => 'rub',
        ]);

        self::assertSame(0, $rub->balanceInt);
        self::assertSame(0, $usd->balanceInt);

        $rub->deposit(10000);

        self::assertSame(10000, $rub->balanceInt);
        self::assertSame(0, $usd->balanceInt);

        $transfer = $rub->exchange($usd, 10000);
        self::assertSame(0, $rub->balanceInt);
        self::assertSame(147, $usd->balanceInt);
        self::assertSame(1.47, (float) $usd->balanceFloat); // $1.47
        self::assertSame(0, (int) $transfer->fee);
        self::assertSame(Transfer::STATUS_EXCHANGE, $transfer->status);

        $transfer = $usd->exchange($rub, $usd->balanceInt);
        self::assertSame(0, $usd->balanceInt);
        self::assertSame(9938, $rub->balanceInt);
        self::assertSame(Transfer::STATUS_EXCHANGE, $transfer->status);
    }

    public function testSafe(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet([
            'name' => 'My USD',
            'slug' => 'usd',
        ]);

        $rub = $user->createWallet([
            'name' => 'Мои рубли',
            'slug' => 'rub',
        ]);

        self::assertSame(0, $rub->balanceInt);
        self::assertSame(0, $usd->balanceInt);

        $transfer = $rub->safeExchange($usd, 10000);
        self::assertNull($transfer);
    }

    public function testExchangeClass(): void
    {
        $service = app(ExchangeService::class);

        self::assertSame('1', $service->convertTo('USD', 'EUR', 1));
        self::assertSame('5', $service->convertTo('USD', 'EUR', 5));
        self::assertSame('27', $service->convertTo('USD', 'EUR', 27));
    }

    public function testRate(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet([
            'name' => 'Dollar USA',
            'slug' => 'my-usd',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);
        self::assertSame($usd->slug, 'my-usd');
        self::assertSame($usd->currency, 'USD');
        self::assertSame($usd->holder_id, $user->id);
        self::assertInstanceOf($usd->holder_type, $user);

        $rub = $user->createWallet([
            'name' => 'RUB',
        ]);
        self::assertSame($rub->slug, 'rub');
        self::assertSame($rub->currency, 'RUB');
        self::assertSame($rub->holder_id, $user->id);
        self::assertInstanceOf($rub->holder_type, $user);

        $superWallet = $user->createWallet([
            'name' => 'Super Wallet',
        ]);
        self::assertSame($superWallet->slug, Str::slug('Super Wallet'));
        self::assertSame($superWallet->currency, Str::upper(Str::slug('Super Wallet')));
        self::assertSame($superWallet->holder_id, $user->id);
        self::assertInstanceOf($superWallet->holder_type, $user);

        $rate = app(ExchangeServiceInterface::class)
            ->convertTo($usd->currency, $rub->currency, 1000)
        ;

        self::assertSame(67610., (float) $rate);
    }

    public function testExchange(): void
    {
        $rate = app(ExchangeServiceInterface::class)
            ->convertTo('USD', 'RUB', 1)
        ;

        self::assertSame(67.61, (float) $rate);

        $rate = app(ExchangeServiceInterface::class)
            ->convertTo('RUB', 'USD', 1)
        ;

        self::assertSame(1 / 67.61, (float) $rate);
    }
}
