<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class DbService.
 *
 * @codeCoverageIgnore
 *
 * @deprecated planned to be removed from the project
 */
class DbService
{
    public function connection(): ConnectionInterface
    {
        return DB::connection(config('wallet.database.connection'));
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param int $attempts
     *
     * @throws Throwable
     *
     * @return mixed
     */
    public function transaction(Closure $callback, $attempts = 1)
    {
        if ($this->connection()->transactionLevel()) {
            return $callback($this);
        }

        return $this->connection()->transaction($callback, $attempts);
    }

    /**
     * Get a new raw query expression.
     *
     * @param mixed $value
     */
    public function raw($value): Expression
    {
        return $this->connection()->raw($value);
    }
}
