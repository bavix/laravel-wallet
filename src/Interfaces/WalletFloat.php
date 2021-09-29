<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;

interface WalletFloat
{
    /**
     * @param float|string $amount
     */
    public function depositFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param float|string $amount
     */
    public function withdrawFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param float|string $amount
     */
    public function forceWithdrawFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param float|string $amount
     */
    public function transferFloat(Wallet $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param float|string $amount
     */
    public function safeTransferFloat(Wallet $wallet, $amount, ?array $meta = null): ?Transfer;

    /**
     * @param float|string $amount
     */
    public function forceTransferFloat(Wallet $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param float|string $amount
     */
    public function canWithdrawFloat($amount): bool;

    /**
     * @return float|int|string
     */
    public function getBalanceFloatAttribute();
}
