<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Closure;
use Illuminate\Database\RecordsNotFoundException;

interface DatabaseInterface
{
    /**
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @return mixed
     */
    public function transaction(Closure $closure);
}
