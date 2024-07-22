<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Models\Transaction;

/**
 * @api
 */
interface TransactionServiceInterface
{
    /**
     * Create a new transaction.
     *
     * @param Wallet $wallet The wallet associated with the transaction.
     * @param string $type The type of the transaction.
     * @param float|int|string $amount The amount of the transaction.
     * @param null|array<mixed> $meta Additional information for the transaction.
     * @param bool $confirmed Whether the transaction is confirmed or not.
     * @return Transaction The created transaction.
     *
     * @throws RecordNotFoundException If the wallet is not found.
     */
    public function makeOne(
        Wallet $wallet,
        string $type,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true
    ): Transaction;

    /**
     * Apply multiple transactions to multiple wallets.
     *
     * This method applies multiple transactions to multiple wallets. It takes an array of wallets and an array of
     * transaction objects as input. It returns an array of transactions.
     *
     * @param non-empty-array<int, Wallet> $wallets An array of wallets to apply the transactions to.
     * @param non-empty-array<int, TransactionDtoInterface> $objects An array of transaction objects.
     * @return non-empty-array<string, Transaction> An array of transactions.
     *
     * @throws RecordNotFoundException If any of the wallets are not found.
     */
    public function apply(array $wallets, array $objects): array;
}
