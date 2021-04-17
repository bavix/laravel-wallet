<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;

interface WalletFloat
{
    public function depositFloat(string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    public function withdrawFloat(string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    public function forceWithdrawFloat(string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    public function transferFloat(Wallet $wallet, string $amount, ?array $meta = null): Transfer;

    public function safeTransferFloat(Wallet $wallet, string $amount, ?array $meta = null): ?Transfer;

    public function forceTransferFloat(Wallet $wallet, $amount, ?array $meta = null): Transfer;

    public function canWithdrawFloat(string $amount): bool;

    public function getBalanceFloatAttribute(): string;
}
