<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Interfaces\ProductInterface;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Factories\ItemFactory;
use Bavix\Wallet\Test\Infra\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\Models\Item;
use Bavix\Wallet\Test\Infra\Models\UserMulti;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Database\Eloquent\Collection;

/**
 * @internal
 */
final class EagerLoadingTest extends TestCase
{
    public function testUuidDuplicate(): void
    {
        $expected = [];

        /** @var Buyer[]|Collection $buyerTimes */
        $buyerTimes = BuyerFactory::times(10)->create();
        foreach ($buyerTimes as $buyerTime) {
            self::assertTrue($buyerTime->wallet->relationLoaded('holder'));
            $buyerTime->deposit(100);
            $expected[] = $buyerTime->wallet->uuid;
        }

        /** @var Buyer[] $buyers */
        $buyers = Buyer::with('wallet')
            ->whereIn('id', $buyerTimes->pluck('id')->toArray())
            ->paginate(10)
        ;

        $uuids = [];
        $balances = [];
        foreach ($buyers as $buyer) {
            self::assertTrue($buyer->relationLoaded('wallet'));
            self::assertTrue($buyer->wallet->relationLoaded('holder'));

            $uuids[] = $buyer->wallet->uuid;
            $balances[] = $buyer->wallet->balanceInt;
        }

        self::assertCount(10, array_unique($uuids));
        self::assertCount(1, array_unique($balances));
        self::assertEquals($expected, $uuids);
    }

    public function testTransferTransactions(): void
    {
        /** @var Buyer $user1 */
        /** @var Buyer $user2 */
        [$user1, $user2] = BuyerFactory::times(2)->create();

        $user1->deposit(1000);
        self::assertSame(1000, $user1->balanceInt);

        $transfer = $user1->transfer($user2, 500);
        self::assertTrue($transfer->relationLoaded('withdraw'));
        self::assertTrue($transfer->relationLoaded('deposit'));

        self::assertTrue($transfer->relationLoaded('from'));
        self::assertTrue($transfer->relationLoaded('to'));

        self::assertTrue($user1->wallet->is($transfer->from));
        self::assertTrue($user2->wallet->is($transfer->to));
    }

    public function testMultiWallets(): void
    {
        /** @var UserMulti $multi */
        $multi = UserMultiFactory::new()->create();
        $multi->createWallet([
            'name' => 'Hello',
        ]);

        $multi->createWallet([
            'name' => 'World',
        ]);

        /** @var UserMulti $user */
        $user = UserMulti::with('wallets')->find($multi->getKey());
        self::assertTrue($user->relationLoaded('wallets'));
        self::assertNotNull($user->getWallet('hello'));
        self::assertNotNull($user->getWallet('world'));
        self::assertTrue($user->getWallet('hello')->relationLoaded('holder'));
        self::assertTrue($user->is($user->getWallet('hello')->holder));
    }

    public function testEagerLoaderPay(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item[] $products */
        $products = ItemFactory::times(50)->create([
            'quantity' => 5,
            'price' => 1,
        ]);
        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getKey();
            self::assertSame(0, $product->balanceInt);
        }

        /** @var ProductInterface[] $products */
        $products = Item::query()->whereKey($productIds)->get()->all();

        $cart = app(Cart::class);
        foreach ($products as $product) {
            $cart = $cart->withItem($product, 5);
        }

        $transfers = $buyer->forcePayCart($cart);
        self::assertSame((int) -$cart->getTotal($buyer), $buyer->balanceInt);
        self::assertCount(250, $transfers);
    }
}
