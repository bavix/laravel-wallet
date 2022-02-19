<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\UserFactory;
use Bavix\Wallet\Test\Infra\Models\User;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
class SafeDealTest extends TestCase
{
    /**
     * @see https://github.com/bavix/laravel-wallet/issues/439
     */
    public function testTransactionResetConfirm(): void
    {
        /**
         * @var User $user1
         * @var User $user2
         */
        [$user1, $user2] = UserFactory::times(2)->create();
        $user1->deposit(1000);

        self::assertSame(1000, $user1->balanceInt);
        app(DatabaseServiceInterface::class)->transaction(static function () use ($user1, $user2) {
            $transfer = $user1->transfer($user2, 500);
            $user1->wallet->resetConfirm($transfer->deposit); // confirm => false
        });

        self::assertSame(500, (int) $user1->transactions()->sum('amount'));
        self::assertSame(500, (int) $user2->transactions()->sum('amount'));

        self::assertSame(500, $user1->balanceInt);
        self::assertSame(0, $user2->balanceInt);
    }
}
