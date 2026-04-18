<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Service\TransactionStateService;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * Test demonstrates TransactionStateService usage pattern.
 *
 * IMPORTANT: This service is NOT automatically registered in core.
 * User must create their custom TransactionStateService and pass it
 * to their custom Assembler.
 *
 * This is "opt-in" approach - no overhead for users who don't need it.
 *
 * @internal
 */
final class StateContextTest extends TestCase
{
    private TransactionStateService $state;

    private RegulatorServiceInterface $regulator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->state = new TransactionStateService();
        $this->regulator = app(RegulatorServiceInterface::class);
    }

    public function testBasicStateTracking(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        $walletId = (int) $wallet->getKey();
        $balance = $this->regulator->amount($wallet);

        $this->state->push('test-1', $walletId, [
            'balance' => $balance,
        ], [
            'balance' => $balance,
        ]);

        self::assertTrue($this->state->has('test-1'));
        self::assertSame([
            'balance' => '0',
        ], $this->state->before('test-1'));
    }

    public function testStateTrackingSequence(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;
        $walletId = (int) $wallet->getKey();

        $this->state->push('seq-1', $walletId, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);
        $this->state->push('seq-2', $walletId, [
            'balance' => '10',
        ], [
            'balance' => '30',
        ]);
        $this->state->push('seq-3', $walletId, [
            'balance' => '30',
        ], [
            'balance' => '35',
        ]);

        self::assertSame([
            'balance' => '0',
        ], $this->state->before('seq-1'));
        self::assertSame([
            'balance' => '10',
        ], $this->state->before('seq-2'));
        self::assertSame([
            'balance' => '30',
        ], $this->state->before('seq-3'));

        self::assertSame([
            'balance' => '10',
        ], $this->state->after('seq-1'));
        self::assertSame([
            'balance' => '30',
        ], $this->state->after('seq-2'));
        self::assertSame([
            'balance' => '35',
        ], $this->state->after('seq-3'));
    }

    public function testRollbackClearsState(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;
        $walletId = (int) $wallet->getKey();

        $this->state->push('rb-1', $walletId, [
            'balance' => '0',
        ], [
            'balance' => '100',
        ]);
        $snapshot = $this->state->snapshot();
        self::assertCount(1, $snapshot);

        $this->state->push('rb-2', $walletId, [
            'balance' => '100',
        ], [
            'balance' => '150',
        ]);
        self::assertCount(2, $this->state->snapshot());

        $this->state->rollback($snapshot);
        self::assertCount(1, $this->state->snapshot());
        self::assertTrue($this->state->has('rb-1'));
        self::assertFalse($this->state->has('rb-2'));
    }

    public function testResetClearsAllState(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;
        $walletId = (int) $wallet->getKey();

        $this->state->push('reset-1', $walletId, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);
        $this->state->push('reset-2', $walletId, [
            'balance' => '10',
        ], [
            'balance' => '20',
        ]);

        self::assertCount(2, $this->state->snapshot());

        $this->state->reset();

        self::assertFalse($this->state->has('reset-1'));
        self::assertFalse($this->state->has('reset-2'));
        self::assertSame([], $this->state->snapshot());
    }

    public function testNoStateLeakageBetweenSeparateWallets(): void
    {
        /** @var Buyer $buyer1 */
        $buyer1 = BuyerFactory::new()->create();

        /** @var Buyer $buyer2 */
        $buyer2 = BuyerFactory::new()->create();

        $wallet1Id = (int) $buyer1->wallet->getKey();
        $wallet2Id = (int) $buyer2->wallet->getKey();

        $this->state->push('batch1', $wallet1Id, [
            'balance' => '0',
        ], [
            'balance' => '100',
        ]);
        self::assertTrue($this->state->has('batch1'));

        $this->state->reset();

        $this->state->push('batch2', $wallet2Id, [
            'balance' => '0',
        ], [
            'balance' => '200',
        ]);

        self::assertFalse($this->state->has('batch1'));
        self::assertTrue($this->state->has('batch2'));
    }

    public function testSnapshotPreservesOriginalState(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;
        $walletId = (int) $wallet->getKey();

        $this->state->push('snap-1', $walletId, [
            'balance' => '0',
        ], [
            'balance' => '50',
        ]);

        $originalSnapshot = $this->state->snapshot();

        $this->state->push('snap-2', $walletId, [
            'balance' => '50',
        ], [
            'balance' => '75',
        ]);

        $this->state->rollback($originalSnapshot);

        self::assertTrue($this->state->has('snap-1'));
        self::assertFalse($this->state->has('snap-2'));
    }

    public function testCustomFieldsSupported(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;
        $walletId = (int) $wallet->getKey();

        $this->state->push('custom', $walletId, [
            'balance' => '100',
            'held_balance' => '50',
            'frozen' => '0',
        ], [
            'balance' => '150',
            'held_balance' => '50',
            'frozen' => '0',
        ]);

        $before = $this->state->before('custom');
        $after = $this->state->after('custom');

        self::assertSame('100', $before['balance']);
        self::assertSame('50', $before['held_balance']);
        self::assertSame('0', $before['frozen']);

        self::assertSame('150', $after['balance']);
        self::assertSame('50', $after['held_balance']);
        self::assertSame('0', $after['frozen']);
    }

    public function testEmptyStateThrowsOnMissingUuid(): void
    {
        $this->expectException(\Bavix\Wallet\Internal\Exceptions\StateNotFoundException::class);

        $this->state->before('nonexistent');
    }

    public function testMultipleRollbacksChain(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;
        $walletId = (int) $wallet->getKey();

        $this->state->push('chain-1', $walletId, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);
        $snap1 = $this->state->snapshot();

        $this->state->push('chain-2', $walletId, [
            'balance' => '10',
        ], [
            'balance' => '25',
        ]);
        $snap2 = $this->state->snapshot();

        $this->state->push('chain-3', $walletId, [
            'balance' => '25',
        ], [
            'balance' => '30',
        ]);

        self::assertCount(3, $this->state->snapshot());

        $this->state->rollback($snap2);
        self::assertTrue($this->state->has('chain-2'));
        self::assertFalse($this->state->has('chain-3'));

        $this->state->rollback($snap1);
        self::assertTrue($this->state->has('chain-1'));
        self::assertFalse($this->state->has('chain-2'));
    }
}
