<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Projector\WalletBatchProjectorInterface;
use Bavix\Wallet\Internal\Repository\TransactionRepositoryInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Bavix\Wallet\Services\AtmServiceInterface;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Services\TransactionServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\PackageModels\TransactionState;
use Bavix\Wallet\Test\Infra\PackageModels\WalletState;
use Bavix\Wallet\Test\Infra\ProjectionTestServiceProvider;
use Bavix\Wallet\Test\Infra\Projectors\WalletStateBatchProjector;
use Bavix\Wallet\Test\Infra\TestCase;
use Bavix\Wallet\Test\Infra\Transform\TransactionStateDtoTransformer;
use Illuminate\Foundation\Application;
use Override;

/**
 * @internal
 */
final class Issue1015ProjectionTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->app?->singleton(TransactionDtoTransformerInterface::class, TransactionStateDtoTransformer::class);
        $this->app?->singleton(WalletBatchProjectorInterface::class, WalletStateBatchProjector::class);

        $this->app?->forgetInstance(TransactionRepositoryInterface::class);
        $this->app?->forgetInstance(AtmServiceInterface::class);
        $this->app?->forgetInstance(TransactionServiceInterface::class);
        $this->app?->forgetInstance(RegulatorServiceInterface::class);
    }

    public function testTransactionStateColumnsCorrectForBatchAcrossWallets(): void
    {
        /** @var Buyer $buyerA */
        $buyerA = BuyerFactory::new()->create();
        /** @var Buyer $buyerB */
        $buyerB = BuyerFactory::new()->create();

        $prepareService = app(PrepareServiceInterface::class);
        $transactionService = app(TransactionServiceInterface::class);

        $uuidA1 = '11111111-1111-1111-1111-111111111111';
        $uuidB1 = '22222222-2222-2222-2222-222222222222';
        $uuidA2 = '33333333-3333-3333-3333-333333333333';
        $uuidB2 = '44444444-4444-4444-4444-444444444444';
        $uuidA3 = '55555555-5555-5555-5555-555555555555';

        $objects = [
            $prepareService->deposit($buyerA, 10, [
                'case' => 'batch',
            ], true, $uuidA1),
            $prepareService->deposit($buyerB, 20, [
                'case' => 'batch',
            ], true, $uuidB1),
            $prepareService->deposit($buyerA, 5, [
                'case' => 'batch',
            ], false, $uuidA2),
            $prepareService->deposit($buyerB, 7, [
                'case' => 'batch',
            ], true, $uuidB2),
            $prepareService->deposit($buyerA, 2, [
                'case' => 'batch',
            ], true, $uuidA3),
        ];

        $transactionService->apply([
            $buyerA->wallet->getKey() => $buyerA,
            $buyerB->wallet->getKey() => $buyerB,
        ], $objects);

        /** @var list<TransactionState> $rows */
        $rows = TransactionState::query()
            ->whereIn('uuid', [$uuidA1, $uuidB1, $uuidA2, $uuidB2, $uuidA3])
            ->get()
            ->all();

        self::assertCount(5, $rows);

        $byUuid = [];
        foreach ($rows as $row) {
            $byUuid[$row->uuid] = $row;
        }

        self::assertSame('0', $byUuid[$uuidA1]->balance_before);
        self::assertSame('10', $byUuid[$uuidA1]->balance_after);

        self::assertSame('0', $byUuid[$uuidB1]->balance_before);
        self::assertSame('20', $byUuid[$uuidB1]->balance_after);

        self::assertSame('10', $byUuid[$uuidA2]->balance_before);
        self::assertSame('10', $byUuid[$uuidA2]->balance_after);

        self::assertSame('20', $byUuid[$uuidB2]->balance_before);
        self::assertSame('27', $byUuid[$uuidB2]->balance_after);

        self::assertSame('10', $byUuid[$uuidA3]->balance_before);
        self::assertSame('12', $byUuid[$uuidA3]->balance_after);

        foreach ($byUuid as $transaction) {
            self::assertNotNull($transaction->state_hash);
            self::assertSame(
                hash(
                    'sha256',
                    $transaction->uuid.':'.$transaction->amount.':'.$transaction->balance_before.':'.$transaction->balance_after
                ),
                $transaction->state_hash,
            );
        }

        $buyerA->refresh();
        $buyerB->refresh();

        self::assertSame(12, $buyerA->balanceInt);
        self::assertSame(27, $buyerB->balanceInt);
    }

    public function testWalletStateColumnsFilledInBalanceUpdatePath(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        $wallet->forceFill([
            'held_balance' => '12',
        ])->saveQuietly();

        $buyer->deposit(88);

        /** @var WalletState $fresh */
        $fresh = WalletState::query()->findOrFail($wallet->getKey());
        self::assertSame('88', $fresh->balance_after);
        self::assertSame('12', $fresh->held_balance);
        self::assertSame(hash('sha256', $fresh->uuid.':88:12'), $fresh->state_hash);
    }

    /**
     * @param Application $app
     * @return non-empty-array<int, string>
     */
    #[Override]
    protected function getPackageProviders($app): array
    {
        $providers = parent::getPackageProviders($app);

        $app['config']->set('wallet.transaction.model', TransactionState::class);
        $app['config']->set('wallet.wallet.model', WalletState::class);

        $providers[] = ProjectionTestServiceProvider::class;

        return $providers;
    }
}
