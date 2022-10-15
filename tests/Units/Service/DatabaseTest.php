<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class DatabaseTest extends TestCase
{
    /**
     * @throws ExceptionInterface
     */
    public function testCheckCode(): void
    {
        $this->expectException(TransactionFailedException::class);
        $this->expectExceptionCode(ExceptionInterface::TRANSACTION_FAILED);
        $this->expectExceptionMessage('Transaction failed. Message: hello');

        app(DatabaseServiceInterface::class)->transaction(static function () {
            throw new \RuntimeException('hello');
        });
    }
}
