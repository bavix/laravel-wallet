<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Internal\DatabaseInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;

/**
 * @internal
 */
final class DatabaseTest extends TestCase
{
    public function testCheckCode(): void
    {
        $this->expectException(TransactionFailedException::class);
        $this->expectExceptionCode(ExceptionInterface::TRANSACTION_FAILED);

        app(DatabaseInterface::class)->transaction(static function () {
            throw new \RuntimeException();
        });
    }
}
