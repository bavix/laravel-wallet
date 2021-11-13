<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;

interface WalletFloatInterface
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
    public function transferFloat(WalletInterface $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param float|string $amount
     */
    public function safeTransferFloat(WalletInterface $wallet, $amount, ?array $meta = null): ?Transfer;

    /**
     * @param float|string $amount
     */
    public function forceTransferFloat(WalletInterface $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param float|string $amount
     */
    public function canWithdrawFloat($amount): bool;

    /**
     * @return float|int|string
     */
    public function getBalanceFloatAttribute();
}
