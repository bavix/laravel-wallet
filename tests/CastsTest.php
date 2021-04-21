<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Factories\UserFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\User;

/**
 * @internal
 */
class CastsTest extends TestCase
{
    public function testModelWallet(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertEquals($buyer->balance, 0);

        self::assertIsInt($buyer->wallet->getKey());
        self::assertEquals($buyer->wallet->getCasts()['id'], 'int');

        config(['wallet.wallet.casts.id' => 'string']);
        self::assertIsString($buyer->wallet->getKey());
        self::assertEquals($buyer->wallet->getCasts()['id'], 'string');
    }

    public function testModelTransfer(): void
    {
        /**
         * @var Buyer $buyer
         * @var User  $user
         */
        $buyer = BuyerFactory::new()->create();
        $user = UserFactory::new()->create();
        self::assertEquals($buyer->balance, 0);
        self::assertEquals($user->balance, 0);

        $deposit = $user->deposit(1000);
        self::assertEquals($user->balance, $deposit->amount);

        $transfer = $user->transfer($buyer, 700);

        self::assertIsInt($transfer->getKey());
        self::assertEquals($transfer->getCasts()['id'], 'int');

        config(['wallet.transfer.casts.id' => 'string']);
        self::assertIsString($transfer->getKey());
        self::assertEquals($transfer->getCasts()['id'], 'string');
    }

    public function testModelTransaction(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertEquals($buyer->balance, 0);
        $deposit = $buyer->deposit(1);

        self::assertIsInt($deposit->getKey());
        self::assertEquals($deposit->getCasts()['id'], 'int');

        config(['wallet.transaction.casts.id' => 'string']);
        self::assertIsString($deposit->getKey());
        self::assertEquals($deposit->getCasts()['id'], 'string');
    }
}
