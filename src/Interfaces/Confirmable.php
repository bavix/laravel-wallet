<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\ConfirmedInvalid;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Models\Transaction;

interface Confirmable
{
    /**
     * @param Transaction $transaction
     *
     * @return bool
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     */
    public function confirm(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function safeConfirm(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     *
     * @return bool
     *
     * @throws ConfirmedInvalid
     */
    public function resetConfirm(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function safeResetConfirm(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     *
     * @return bool
     *
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     */
    public function forceConfirm(Transaction $transaction): bool;
}
