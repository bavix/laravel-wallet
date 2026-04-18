<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Projector\WalletBatchProjectorInterface;
use Bavix\Wallet\Internal\Repository\TransactionRepositoryInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Bavix\Wallet\Services\AtmServiceInterface;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Services\TransactionServiceInterface;
use Bavix\Wallet\Test\Infra\Assembler\TransactionDtoAssemblerStateAware;
use Bavix\Wallet\Test\Infra\Factories\BuyerStateIsoFactory;
use Bavix\Wallet\Test\Infra\Models\BuyerStateIso;
use Bavix\Wallet\Test\Infra\PackageModels\TransactionStateIsolated;
use Bavix\Wallet\Test\Infra\PackageModels\WalletStateIsolated;
use Bavix\Wallet\Test\Infra\ProjectionTestServiceProvider;
use Bavix\Wallet\Test\Infra\Projectors\WalletStateBatchProjector;
use Bavix\Wallet\Test\Infra\TestCase;
use Bavix\Wallet\Test\Infra\Transform\TransactionStateDtoTransformer;
use Illuminate\Foundation\Application;
use Override;

/**
 * @internal
 */
final class Issue1015IsolatedTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->app?->singleton(TransactionDtoTransformerInterface::class, TransactionStateDtoTransformer::class);
        $this->app?->singleton(WalletBatchProjectorInterface::class, WalletStateBatchProjector::class);

        $this->app?->forgetInstance(PrepareServiceInterface::class);
        $this->app?->forgetInstance(TransactionRepositoryInterface::class);
        $this->app?->forgetInstance(AtmServiceInterface::class);
        $this->app?->forgetInstance(TransactionServiceInterface::class);
        $this->app?->forgetInstance(RegulatorServiceInterface::class);
    }

    public function testStateAwareWithUnconfirmedOnlyWallet(): void
    {
        $this->enableStateAwareAssembler();

        /** @var BuyerStateIso $buyerConfirmed */
        $buyerConfirmed = BuyerStateIsoFactory::new()->create();
        /** @var BuyerStateIso $buyerUnconfirmed */
        $buyerUnconfirmed = BuyerStateIsoFactory::new()->create();

        $prepareService = app(PrepareServiceInterface::class);
        $transactionService = app(TransactionServiceInterface::class);

        $uuidC1 = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
        $uuidU1 = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';
        $uuidU2 = 'cccccccc-cccc-cccc-cccc-cccccccccccc';

        $objects = [
            $prepareService->deposit($buyerConfirmed, 100, [
                'case' => 'confirmed',
            ], true, $uuidC1),
            $prepareService->deposit($buyerUnconfirmed, 50, [
                'case' => 'unconfirmed',
            ], false, $uuidU1),
            $prepareService->withdraw($buyerUnconfirmed, 30, [
                'case' => 'unconfirmed',
            ], false, $uuidU2),
        ];

        $transactionService->apply([
            $buyerConfirmed->wallet->getKey() => $buyerConfirmed,
            $buyerUnconfirmed->wallet->getKey() => $buyerUnconfirmed,
        ], $objects);

        /** @var list<TransactionStateIsolated> $rows */
        $rows = TransactionStateIsolated::query()
            ->whereIn('uuid', [$uuidC1, $uuidU1, $uuidU2])
            ->get()
            ->all();

        self::assertCount(3, $rows);

        $byUuid = [];
        foreach ($rows as $row) {
            $byUuid[$row->uuid] = $row;
        }

        self::assertSame('0', $byUuid[$uuidC1]->balance_before);
        self::assertSame('100', $byUuid[$uuidC1]->balance_after);

        self::assertSame('0', $byUuid[$uuidU1]->balance_before);
        self::assertSame('0', $byUuid[$uuidU1]->balance_after);

        self::assertSame('0', $byUuid[$uuidU2]->balance_before);
        self::assertSame('0', $byUuid[$uuidU2]->balance_after);

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

        $buyerConfirmed->refresh();
        $buyerUnconfirmed->refresh();

        self::assertSame(100, $buyerConfirmed->balanceInt);
        self::assertSame(0, $buyerUnconfirmed->balanceInt);
    }

    public function testStateAwareBatchMixedConfirmedAndUnconfirmedPerWallet(): void
    {
        $this->enableStateAwareAssembler();

        /** @var BuyerStateIso $buyerA */
        $buyerA = BuyerStateIsoFactory::new()->create();
        /** @var BuyerStateIso $buyerB */
        $buyerB = BuyerStateIsoFactory::new()->create();

        $prepareService = app(PrepareServiceInterface::class);
        $transactionService = app(TransactionServiceInterface::class);

        $uuidA1 = 'dddddddd-dddd-dddd-dddd-dddddddddddd';
        $uuidA2 = 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee';
        $uuidB1 = 'ffffffff-ffff-ffff-ffff-ffffffffffff';
        $uuidB2 = '11111111-2222-3333-4444-555555555555';

        $objects = [
            $prepareService->deposit($buyerA, 10, [], true, $uuidA1),
            $prepareService->deposit($buyerA, 5, [], false, $uuidA2),
            $prepareService->deposit($buyerB, 0, [], false, $uuidB1),
            $prepareService->deposit($buyerB, 20, [], true, $uuidB2),
        ];

        $transactionService->apply([
            $buyerA->wallet->getKey() => $buyerA,
            $buyerB->wallet->getKey() => $buyerB,
        ], $objects);

        /** @var list<TransactionStateIsolated> $rows */
        $rows = TransactionStateIsolated::query()
            ->whereIn('uuid', [$uuidA1, $uuidA2, $uuidB1, $uuidB2])
            ->get()
            ->all();

        self::assertCount(4, $rows);

        $byUuid = [];
        foreach ($rows as $row) {
            $byUuid[$row->uuid] = $row;
        }

        self::assertSame('0', $byUuid[$uuidA1]->balance_before);
        self::assertSame('10', $byUuid[$uuidA1]->balance_after);

        self::assertSame('10', $byUuid[$uuidA2]->balance_before);
        self::assertSame('10', $byUuid[$uuidA2]->balance_after);

        self::assertSame('0', $byUuid[$uuidB1]->balance_before);
        self::assertSame('0', $byUuid[$uuidB1]->balance_after);

        self::assertSame('0', $byUuid[$uuidB2]->balance_before);
        self::assertSame('20', $byUuid[$uuidB2]->balance_after);

        $buyerA->refresh();
        $buyerB->refresh();

        self::assertSame(10, $buyerA->balanceInt);
        self::assertSame(20, $buyerB->balanceInt);
    }

    public function testStateAwareOffOnIsolatedTables(): void
    {
        $this->disableStateAwareAssembler();

        /** @var BuyerStateIso $buyer */
        $buyer = BuyerStateIsoFactory::new()->create();

        $prepareService = app(PrepareServiceInterface::class);
        $transactionService = app(TransactionServiceInterface::class);

        $uuid = '99999999-9999-9999-9999-999999999999';

        $objects = [
            $prepareService->deposit($buyer, 42, [
                'case' => 'off',
            ], true, $uuid),
        ];

        $transactionService->apply([
            $buyer->wallet->getKey() => $buyer,
        ], $objects);

        /** @var list<TransactionStateIsolated> $rows */
        $rows = TransactionStateIsolated::query()
            ->whereIn('uuid', [$uuid])
            ->get()
            ->all();

        self::assertCount(1, $rows);
        self::assertNull($rows[0]->balance_before);
        self::assertNull($rows[0]->balance_after);
        self::assertNull($rows[0]->state_hash);

        $buyer->refresh();
        self::assertSame(42, $buyer->balanceInt);
    }

    /**
     * @param Application $app
     * @return non-empty-array<int, string>
     */
    #[Override]
    protected function getPackageProviders($app): array
    {
        $providers = parent::getPackageProviders($app);

        $app['config']->set('wallet.transaction.model', TransactionStateIsolated::class);
        $app['config']->set('wallet.wallet.model', WalletStateIsolated::class);

        $providers[] = ProjectionTestServiceProvider::class;

        return $providers;
    }

    private function enableStateAwareAssembler(): void
    {
        $this->app?->singleton(TransactionDtoAssemblerInterface::class, TransactionDtoAssemblerStateAware::class);

        $this->app?->forgetInstance(PrepareServiceInterface::class);
        $this->app?->forgetInstance(TransactionServiceInterface::class);
    }

    private function disableStateAwareAssembler(): void
    {
        $this->app?->singleton(TransactionDtoAssemblerInterface::class, TransactionDtoAssembler::class);

        $this->app?->forgetInstance(PrepareServiceInterface::class);
        $this->app?->forgetInstance(TransactionServiceInterface::class);
    }
}
