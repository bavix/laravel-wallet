<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transaction;

interface Confirmable
{

    /**
     * @param Transaction $transaction
     * @return bool
     */
    public function confirm(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     * @return bool
     */
    public function safeConfirm(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     * @return bool
     */
    public function forceConfirm(Transaction $transaction): bool;

}
