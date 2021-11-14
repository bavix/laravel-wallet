<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Test\Infra\Factories\UserFactory;
use Bavix\Wallet\Test\Infra\Models\User;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
class ModelTableTest extends TestCase
{
    public function testWalletTableName(): void
    {
        /** @var User $user */
        $user = UserFactory::new()->create();
        self::assertSame('wallet', $user->wallet->getTable());
    }

    public function testTransactionTableName(): void
    {
        /** @var User $user */
        $user = UserFactory::new()->create();
        $transaction = $user->deposit(100);
        self::assertSame('transaction', $transaction->getTable());
    }

    public function testTransferTableName(): void
    {
        /**
         * @var User $user1
         * @var User $user2
         */
        [$user1, $user2] = UserFactory::times(2)->create();
        $user1->deposit(1000);
        $transfer = $user1->transfer($user2, 1000);
        self::assertSame('transfer', $transfer->getTable());
    }
}
