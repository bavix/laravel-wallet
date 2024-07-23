<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\RecordsNotFoundException;

interface Exchangeable
{
    /**
     * Exchange currency from this wallet to another wallet.
     *
     * @param Wallet $to The wallet to exchange the currency to.
     * @param int|non-empty-string $amount The amount to exchange.
     * @param ExtraDtoInterface|array<mixed>|null $meta The extra data for the transaction.
     * @return Transfer The created transfer.
     *
     * @throws BalanceIsEmpty             if the wallet does not have enough funds to make the exchange.
     * @throws InsufficientFunds          if the wallet does not have enough funds to make the exchange.
     * @throws RecordNotFoundException    if the wallet does not exist.
     * @throws RecordsNotFoundException   if the wallet does not exist.
     * @throws TransactionFailedException if the transaction fails.
     * @throws ExceptionInterface         if an unexpected error occurs.
     */
    public function exchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer;

    /**
     * Safely exchanges currency from this wallet to another wallet.
     *
     * If an error occurs during the process, null is returned.
     *
     * @param Wallet $to The wallet to exchange the currency to.
     * @param int|non-empty-string $amount The amount to exchange.
     * @param ExtraDtoInterface|array<mixed>|null $meta The extra data for the transaction.
     * @return null|Transfer The created transfer, or null if an error occurred.
     */
    public function safeExchange(
        Wallet $to,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): ?Transfer;

    /**
     * Force exchange currency from this wallet to another wallet.
     *
     * This method will throw an exception if the exchange is not possible.
     *
     * @param Wallet $to The wallet to exchange the currency to.
     * @param int|non-empty-string $amount The amount to exchange.
     * @param ExtraDtoInterface|array<mixed>|null $meta The extra data for the transaction.
     * @return Transfer The created transfer.
     *
     * @throws RecordNotFoundException If the wallet does not exist.
     * @throws RecordsNotFoundException If the wallet does not exist.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an unexpected error occurs.
     *
     * @see Exchangeable::exchange()
     */
    public function forceExchange(
        Wallet $to,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): Transfer;
}
