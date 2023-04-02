<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;

final class TransactionQuery
{
    public const TYPE_DEPOSIT = Transaction::TYPE_DEPOSIT;

    public const TYPE_WITHDRAW = Transaction::TYPE_WITHDRAW;

    /**
     * @param self::TYPE_DEPOSIT|self::TYPE_WITHDRAW $type
     * @param array<mixed>|null $meta
     */
    private function __construct(
        private readonly string $type,
        private readonly Wallet $wallet,
        private readonly float|int|string $amount,
        private readonly ?array $meta,
        private readonly bool $confirmed,
        private readonly ?string $uuid
    ) {
    }

    /**
     * @param array<mixed>|null $meta
     */
    public static function createDeposit(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true,
        ?string $uuid = null
    ): self {
        return new self(self::TYPE_DEPOSIT, $wallet, $amount, $meta, $confirmed, $uuid);
    }

    /**
     * @param array<mixed>|null $meta
     */
    public static function createWithdraw(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true,
        ?string $uuid = null
    ): self {
        return new self(self::TYPE_WITHDRAW, $wallet, $amount, $meta, $confirmed, $uuid);
    }

    /**
     * @return self::TYPE_DEPOSIT|self::TYPE_WITHDRAW
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getAmount(): float|int|string
    {
        return $this->amount;
    }

    /**
     * @return array<mixed>|null
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }
}
