<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Enums\TransactionType;
use Bavix\Wallet\Interfaces\Wallet;

final readonly class TransactionQuery implements TransactionQueryInterface
{
    /**
     * @param array<mixed>|null $meta
     */
    private function __construct(
        private TransactionType $type,
        private Wallet $wallet,
        private float|int|string $amount,
        private ?array $meta,
        private bool $confirmed,
        private ?string $uuid
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
        return new self(TransactionType::Deposit, $wallet, $amount, $meta, $confirmed, $uuid);
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
        return new self(TransactionType::Withdraw, $wallet, $amount, $meta, $confirmed, $uuid);
    }

    public function getType(): TransactionType
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
