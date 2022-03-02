<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transaction;

interface Confirmable
{
    public function confirm(Transaction $transaction): bool;

    public function safeConfirm(Transaction $transaction): bool;

    public function resetConfirm(Transaction $transaction): bool;

    public function safeResetConfirm(Transaction $transaction): bool;

    public function forceConfirm(Transaction $transaction): bool;
}
