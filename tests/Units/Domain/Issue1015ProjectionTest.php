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
use Bavix\Wallet\Test\Infra\Projectors\WalletStateBatchProjector;
use Bavix\Wallet\Test\Infra\ProjectionTestCase;
use Bavix\Wallet\Test\Infra\Transform\TransactionStateDtoTransformer;
use Override;

/**
 * @internal
 */
final class Issue1015ProjectionTest extends ProjectionTestCase
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

        $objects = [
            $prepareService->deposit($buyerA, 10, ['case' => 'batch'], true, 'batch-a-1'),
            $prepareService->deposit($buyerB, 20, ['case' => 'batch'], true, 'batch-b-1'),
            $prepareService->deposit($buyerA, 5, ['case' => 'batch'], false, 'batch-a-2-unconfirmed'),
            $prepareService->deposit($buyerB, 7, ['case' => 'batch'], true, 'batch-b-2'),
            $prepareService->deposit($buyerA, 2, ['case' => 'batch'], true, 'batch-a-3'),
        ];

        $transactionService->apply([
            $buyerA->wallet->getKey() => $buyerA,
            $buyerB->wallet->getKey() => $buyerB,
        ], $objects);

        /** @var list<TransactionState> $rows */
        $rows = TransactionState::query()
            ->whereIn('uuid', [
                'batch-a-1',
                'batch-b-1',
                'batch-a-2-unconfirmed',
                'batch-b-2',
                'batch-a-3',
            ])
            ->get()
            ->all();

        self::assertCount(5, $rows);

        $byUuid = [];
        foreach ($rows as $row) {
            $byUuid[$row->uuid] = $row;
        }

        self::assertSame('0', $byUuid['batch-a-1']->balance_before);
        self::assertSame('10', $byUuid['batch-a-1']->balance_after);

        self::assertSame('0', $byUuid['batch-b-1']->balance_before);
        self::assertSame('20', $byUuid['batch-b-1']->balance_after);

        self::assertSame('10', $byUuid['batch-a-2-unconfirmed']->balance_before);
        self::assertSame('10', $byUuid['batch-a-2-unconfirmed']->balance_after);

        self::assertSame('20', $byUuid['batch-b-2']->balance_before);
        self::assertSame('27', $byUuid['batch-b-2']->balance_after);

        self::assertSame('10', $byUuid['batch-a-3']->balance_before);
        self::assertSame('12', $byUuid['batch-a-3']->balance_after);

        foreach ($byUuid as $transaction) {
            self::assertNotNull($transaction->state_hash);
            self::assertSame(
                hash('sha256', $transaction->uuid.':'.$transaction->amount.':'.$transaction->balance_before.':'.$transaction->balance_after),
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
}
