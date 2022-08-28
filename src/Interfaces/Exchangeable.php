<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\RecordsNotFoundException;

interface Exchangeable
{
    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function exchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer;

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     */
    public function safeExchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): ?Transfer;

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceExchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer;
}
