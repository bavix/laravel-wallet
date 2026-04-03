<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Models\Purchase;
use Bavix\Wallet\Test\Infra\Factories\ManagerFactory;
use Bavix\Wallet\Test\Infra\Factories\UserFactory;
use Bavix\Wallet\Test\Infra\Models\Manager;
use Bavix\Wallet\Test\Infra\Models\User;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Support\Facades\DB;

/**
 * @internal
 */
final class ModelTableTest extends TestCase
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

        /** @var Manager $manager */
        $manager = ManagerFactory::new()->create();
        $user2->transfer($manager, 1000);
        self::assertSame(1000, $manager->balanceInt);
    }

    public function testPurchaseTableName(): void
    {
        self::assertSame('purchase', app(Purchase::class)->getTable());
    }

    public function testRegularTransferDoesNotWritePurchaseLedger(): void
    {
        /** @var User $from */
        $from = UserFactory::new()->create();
        /** @var User $to */
        $to = UserFactory::new()->create();

        $from->deposit(100);
        $transfer = $from->transfer($to, 30);

        /** @var string $purchaseTable */
        $purchaseTable = config('wallet.purchase.table', 'purchase');
        self::assertFalse(DB::table($purchaseTable)->where('transfer_id', $transfer->getKey())->exists());
    }

    public function testPurchaseModelCastsAndRelations(): void
    {
        /** @var User $buyer */
        $buyer = UserFactory::new()->create();
        /** @var User $seller */
        $seller = UserFactory::new()->create();

        $buyer->deposit(50);
        $transfer = $buyer->transfer($seller, 10);

        app(Purchase::class)->newQuery()->create([
            'transfer_id' => $transfer->getKey(),
            'from_id' => $transfer->from_id,
            'to_id' => $transfer->to_id,
            'status' => $transfer->status,
        ]);

        /** @var Purchase $purchase */
        $purchase = app(Purchase::class)->newQuery()->firstOrFail();

        self::assertSame($transfer->getKey(), $purchase->transfer->getKey());
        self::assertSame($transfer->from_id, $purchase->from->getKey());
        self::assertSame($transfer->to_id, $purchase->to->getKey());
        self::assertSame($transfer->status, $purchase->status);
    }
}
