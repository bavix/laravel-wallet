<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Exceptions;

use InvalidArgumentException;

final class TransactionRollbackException extends InvalidArgumentException implements ExceptionInterface
{
    public function __construct(
        private mixed $result
    ) {
        parent::__construct();
    }

    public function getResult(): mixed
    {
        return $this->result;
    }
}
