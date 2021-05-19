<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Contracts\LockInterface;
use Bavix\Wallet\Contracts\MathInterface;
use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Objects\EmptyLock;
use Bavix\Wallet\Objects\Operation;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\LockService;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Test\Common\Models\Transaction;
use Bavix\Wallet\Test\Common\Models\Transfer;
use Bavix\Wallet\Test\Common\Models\Wallet;

/**
 * @internal
 */
class SingletonTest extends TestCase
{
    /** @dataProvider newables */
    public function testNew(string $first, string $second): void
    {
        self::assertNotSame($this->getRefId($first), $this->getRefId($second));
    }

    public function newables(): iterable
    {
        yield [Transaction::class, Transaction::class];
        yield [Transfer::class, Transfer::class];
        yield [Wallet::class, Wallet::class];

        yield [Bring::class, Bring::class];
        yield [Cart::class, Cart::class];
        yield [EmptyLock::class, EmptyLock::class];
        yield [Operation::class, Operation::class];
    }

    /** @dataProvider singletons */
    public function testSingleton(string $first, string $second): void
    {
        self::assertSame($this->getRefId($first), $this->getRefId($second));
    }

    public function singletons(): iterable
    {
        // lock
        yield [LockInterface::class, LockInterface::class];

        // math
        yield [MathInterface::class, MathInterface::class];
        yield [MathInterface::class, Mathable::class]; // deprecated

        yield [WalletService::class, WalletService::class];

        yield [Rateable::class, Rateable::class];
        yield [Mathable::class, Mathable::class];
        yield [DbService::class, DbService::class];
        yield [LockService::class, LockService::class];
        yield [ExchangeService::class, ExchangeService::class];
        yield [CommonService::class, CommonService::class];
    }

    protected function getRefId(string $object): int
    {
        return spl_object_id(app($object));
    }
}
