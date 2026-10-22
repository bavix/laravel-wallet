<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Test\Infra\PackageModels\Transaction;
use Bavix\Wallet\Test\Infra\PackageModels\Transfer;
use Bavix\Wallet\Test\Infra\PackageModels\Wallet;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class SingletonTest extends TestCase
{
    public function test_cart(): void
    {
        self::assertNotSame($this->getRefId(Cart::class), $this->getRefId(Cart::class));
    }

    public function test_math_interface(): void
    {
        self::assertSame($this->getRefId(MathServiceInterface::class), $this->getRefId(MathServiceInterface::class));
    }

    public function test_transaction(): void
    {
        self::assertNotSame($this->getRefId(Transaction::class), $this->getRefId(Transaction::class));
    }

    public function test_transfer(): void
    {
        self::assertNotSame($this->getRefId(Transfer::class), $this->getRefId(Transfer::class));
    }

    public function test_wallet(): void
    {
        self::assertNotSame($this->getRefId(Wallet::class), $this->getRefId(Wallet::class));
    }

    public function test_database_service(): void
    {
        self::assertSame(
            $this->getRefId(DatabaseServiceInterface::class),
            $this->getRefId(DatabaseServiceInterface::class)
        );
    }

    private function getRefId(string $object): string
    {
        return spl_object_hash(app($object));
    }
}
