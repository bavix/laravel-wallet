<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class AtomicServiceTest extends TestCase
{
    public function testBlock(): void
    {
        $atomic = app(AtomicServiceInterface::class);

        /** @var Buyer $user1 */
        /** @var Buyer $user2 */
        [$user1, $user2] = BuyerFactory::times(2)->create();

        $user1->deposit(1000);

        $atomic->blocks(
            [$user1->wallet, $user2->wallet],
            fn () => collect([
                fn () => $user1->transfer($user2, 500),
                fn () => $user1->transfer($user2, 500),
                fn () => $user2->transfer($user1, 500),
            ])
                ->map(fn ($fx) => $fx()),
        );

        self::assertSame(1, $user2->transfers()->count());
        self::assertSame(2, $user2->receivedTransfers()->count());
        self::assertSame(2, $user1->transfers()->count());
        self::assertSame(1, $user1->receivedTransfers()->count());
        self::assertSame(3, $user2->transactions()->count());
        self::assertSame(4, $user1->transactions()->count());

        self::assertSame(500, $user1->balanceInt);
        self::assertSame(500, $user2->balanceInt);
    }

    public function testBlockIter3(): void
    {
        $atomicService = app(AtomicServiceInterface::class);

        /** @var Buyer $user */
        $user = BuyerFactory::new()->create();
        $iterations = 3;

        self::assertSame(0, $user->balanceInt);

        for ($i = 1; $i <= $iterations; $i++) {
            $atomicService->block($user, function () use ($user) {
                $user->forceWithdraw(1000);
                $user->forceWithdraw(1000);
                $user->forceWithdraw(1000);
                $user->deposit(5000);
            });
        }

        self::assertSame($iterations * 2000, $user->balanceInt);
    }

    /**
     * Tests the rollback functionality of the AtomicService.
     *
     * This test creates a new Buyer and deposits 1000 units into their wallet. Then, it attempts to
     * withdraw 3000 units from the wallet within an atomic block. Since there are not enough funds,
     * an exception is thrown. The test then checks that the balance of the wallet has not changed.
     */
    public function testRollback(): void
    {
        // Create a new instance of the AtomicService
        $atomic = app(AtomicServiceInterface::class);

        // Create a new Buyer and deposit 1000 units into their wallet
        /** @var Buyer $user */
        $user = BuyerFactory::new()->create();
        $user->deposit(1000);

        // Check that the balance of the wallet is 1000 units
        $this->assertSame(1000, $user->balanceInt);

        try {
            // Start an atomic block and attempt to withdraw 3000 units from the wallet
            $atomic->block($user, function () use ($user) {
                // Withdraw 1000 units from the wallet
                $user->forceWithdraw(1000);
                // Withdraw 1000 units from the wallet
                $user->forceWithdraw(1000);
                // Withdraw 1000 units from the wallet
                $user->forceWithdraw(1000);
                // Deposit 5000 units into the wallet
                $user->deposit(5000);

                // Throw an exception to simulate an error
                throw new \Exception();
            });

            // This should not be reached
            $this->assertTrue(false); // check
        } catch (\Throwable $e) {
            // Intentionally left empty
        }

        // Refresh the balance of the wallet to ensure it has not changed
        $this->assertTrue($user->wallet->refreshBalance()); // check

        // Retrieve the Buyer from the database and check that the balance is still 1000 units

        /** @var Buyer $userFromDb */
        $userFromDb = Buyer::find($user->getKey());

        // Check that the balance of the wallet is 1000 units
        $this->assertSame(1000, $userFromDb->balanceInt);
        // Check that the balance of the wallet is 1000 units
        $this->assertSame(1000, $user->balanceInt);
    }
}
