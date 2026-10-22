<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\StateNotFoundException;
use Bavix\Wallet\Internal\Service\TransactionStateService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TransactionStateServiceTest extends TestCase
{
    private TransactionStateService $service;

    protected function setUp(): void
    {
        $this->service = new TransactionStateService;
    }

    public function test_push_and_has(): void
    {
        self::assertFalse($this->service->has('u1'));

        $this->service->push('u1', 1, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);

        self::assertTrue($this->service->has('u1'));
    }

    public function test_before_returns_state(): void
    {
        $this->service->push('u1', 1, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);

        self::assertSame([
            'balance' => '0',
        ], $this->service->before('u1'));
    }

    public function test_after_returns_state(): void
    {
        $this->service->push('u1', 1, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);

        self::assertSame([
            'balance' => '10',
        ], $this->service->after('u1'));
    }

    public function test_before_throws_for_missing_uuid(): void
    {
        $this->expectException(StateNotFoundException::class);

        $this->service->before('nonexistent');
    }

    public function test_after_throws_for_missing_uuid(): void
    {
        $this->expectException(StateNotFoundException::class);

        $this->service->after('nonexistent');
    }

    public function test_custom_state_fields(): void
    {
        $this->service->push('u1', 1, [
            'balance' => '0',
            'held_balance' => '5',
            'frozen' => '3',
        ], [
            'balance' => '10',
            'held_balance' => '5',
            'frozen' => '0',
        ]);

        $before = $this->service->before('u1');
        $after = $this->service->after('u1');

        self::assertSame('0', $before['balance']);
        self::assertSame('5', $before['held_balance']);
        self::assertSame('3', $before['frozen']);

        self::assertSame('10', $after['balance']);
        self::assertSame('5', $after['held_balance']);
        self::assertSame('0', $after['frozen']);
    }

    public function test_multiple_entries(): void
    {
        $this->service->push('u1', 1, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);
        $this->service->push('u2', 1, [
            'balance' => '10',
        ], [
            'balance' => '25',
        ]);
        $this->service->push('u3', 2, [
            'balance' => '0',
        ], [
            'balance' => '50',
        ]);

        self::assertTrue($this->service->has('u1'));
        self::assertTrue($this->service->has('u2'));
        self::assertTrue($this->service->has('u3'));
        self::assertFalse($this->service->has('u4'));

        self::assertSame('0', $this->service->before('u1')['balance']);
        self::assertSame('10', $this->service->before('u2')['balance']);
        self::assertSame('0', $this->service->before('u3')['balance']);
    }

    public function test_snapshot_captures_all_entries(): void
    {
        $this->service->push('u1', 1, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);
        $this->service->push('u2', 2, [
            'balance' => '0',
        ], [
            'balance' => '20',
        ]);

        $snapshot = $this->service->snapshot();

        self::assertCount(2, $snapshot);
        self::assertSame(1, $snapshot['u1']['walletId']);
        self::assertSame([
            'balance' => '0',
        ], $snapshot['u1']['before']);
        self::assertSame([
            'balance' => '10',
        ], $snapshot['u1']['after']);
        self::assertSame(2, $snapshot['u2']['walletId']);
    }

    public function test_rollback_restores_state(): void
    {
        $this->service->push('u1', 1, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);
        $snapshot = $this->service->snapshot();

        $this->service->push('u2', 2, [
            'balance' => '0',
        ], [
            'balance' => '20',
        ]);
        $this->service->push('u3', 1, [
            'balance' => '10',
        ], [
            'balance' => '15',
        ]);

        self::assertTrue($this->service->has('u2'));
        self::assertTrue($this->service->has('u3'));

        $this->service->rollback($snapshot);

        self::assertTrue($this->service->has('u1'));
        self::assertFalse($this->service->has('u2'));
        self::assertFalse($this->service->has('u3'));
        self::assertSame('0', $this->service->before('u1')['balance']);
        self::assertSame('10', $this->service->after('u1')['balance']);
    }

    public function test_rollback_to_empty_snapshot(): void
    {
        $this->service->push('u1', 1, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);

        $emptySnapshot = [];

        $this->service->rollback($emptySnapshot);

        self::assertFalse($this->service->has('u1'));
    }

    public function test_reset_clears_all(): void
    {
        $this->service->push('u1', 1, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);
        $this->service->push('u2', 2, [
            'balance' => '0',
        ], [
            'balance' => '20',
        ]);

        $this->service->reset();

        self::assertFalse($this->service->has('u1'));
        self::assertFalse($this->service->has('u2'));
        self::assertSame([], $this->service->snapshot());
    }

    public function test_snapshot_after_reset_is_empty(): void
    {
        $this->service->push('u1', 1, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);
        $this->service->reset();

        self::assertSame([], $this->service->snapshot());
    }

    public function test_multiple_snapshots_with_rollback_chaining(): void
    {
        $this->service->push('u1', 1, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);
        $snap1 = $this->service->snapshot();

        $this->service->push('u2', 1, [
            'balance' => '10',
        ], [
            'balance' => '25',
        ]);
        $snap2 = $this->service->snapshot();

        $this->service->push('u3', 1, [
            'balance' => '25',
        ], [
            'balance' => '30',
        ]);

        self::assertCount(3, $this->service->snapshot());

        $this->service->rollback($snap2);
        self::assertCount(2, $this->service->snapshot());
        self::assertSame('25', $this->service->after('u2')['balance']);

        $this->service->rollback($snap1);
        self::assertCount(1, $this->service->snapshot());
        self::assertFalse($this->service->has('u2'));
        self::assertSame('10', $this->service->after('u1')['balance']);
    }

    public function test_push_overwrites_same_uuid(): void
    {
        $this->service->push('u1', 1, [
            'balance' => '0',
        ], [
            'balance' => '10',
        ]);
        $this->service->push('u1', 1, [
            'balance' => '5',
        ], [
            'balance' => '15',
        ]);

        self::assertSame('5', $this->service->before('u1')['balance']);
        self::assertSame('15', $this->service->after('u1')['balance']);
    }
}
