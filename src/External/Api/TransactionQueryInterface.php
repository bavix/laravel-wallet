<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;

interface TransactionQueryInterface
{
    public const TYPE_DEPOSIT = Transaction::TYPE_DEPOSIT;

    public const TYPE_WITHDRAW = Transaction::TYPE_WITHDRAW;

    /**
     * @return self::TYPE_DEPOSIT|self::TYPE_WITHDRAW
     */
    public function getType(): string;

    public function getWallet(): Wallet;

    public function getAmount(): float|int|string;

    /**
     * @return array<mixed>|null
     */
    public function getMeta(): ?array;

    public function isConfirmed(): bool;

    public function getUuid(): ?string;
}
