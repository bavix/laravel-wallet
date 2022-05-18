<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\External\Dto\Cost;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\ExchangeServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\ItemRubFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemUsdFactory;
use Bavix\Wallet\Test\Infra\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Infra\Models\ItemRub;
use Bavix\Wallet\Test\Infra\Models\ItemUsd;
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

    public function testPayItemUsd(): void
    {
        /**
         * @var UserMulti $user
         * @var ItemUsd   $product
         */
        $user = UserMultiFactory::new()->create();
        $product = ItemUsdFactory::new()->create([
            'price' => 42,
        ]);

        $cart = app(Cart::class)
            ->withItem($product) // $42
            ->withItem($product, pricePerItem: new Cost(1, 'USD'))
        ;

        $usdWallet = $user->createWallet([
            'name' => 'dollar bill',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);
        $rubWallet = $user->createWallet([
            'name' => 'ruble bill',
            'meta' => [
                'currency' => 'RUB',
            ],
        ]);

        self::assertSame(43, (int) $cart->getTotal($usdWallet));
        self::assertSame(2906, (int) $cart->getTotal($rubWallet));

        $usdWallet->deposit(43);
        self::assertSame(43, $usdWallet->balanceInt);
        self::assertSame('USD', $usdWallet->currency);

        $rubWallet->deposit(2906);
        self::assertSame(2906, $rubWallet->balanceInt);
        self::assertSame('RUB', $rubWallet->currency);

        self::assertNotNull($usdWallet->payCart($cart));
        self::assertNotNull($rubWallet->payCart($cart));

        self::assertSame(0, $usdWallet->balanceInt);
        self::assertSame(0, $rubWallet->balanceInt);

        self::assertTrue($usdWallet->refundCart($cart));
        self::assertTrue($rubWallet->refundCart($cart));
    }

    public function testPayItemRub(): void
    {
        /**
         * @var UserMulti $user
         * @var ItemRub   $product
         *
         * 100₽=$1.47
         */
        $user = UserMultiFactory::new()->create();
        $product = ItemRubFactory::new()->create([
            'price' => 10000,
        ]);

        $usdWallet = $user->createWallet([
            'name' => 'dollar bill',
            'meta' => [
                'currency' => 'USD',
            ],
        ]);

        $productCost = $product->getAmountProduct($usdWallet);

        self::assertSame(10000, (int) $productCost->getValue());
        self::assertSame('RUB', $productCost->getCurrency());

        $usdWallet->deposit(147); // $1.47
        self::assertSame(147, $usdWallet->balanceInt);
        self::assertSame(1.47, $usdWallet->balanceFloatNum);

        self::assertNotNull($usdWallet->safePay($product));

        self::assertSame(0, $usdWallet->balanceInt);
        self::assertSame(0., $usdWallet->balanceFloatNum);

        self::assertTrue($usdWallet->refund($product));

        self::assertSame(1.47, $usdWallet->balanceFloatNum);
    }
}
