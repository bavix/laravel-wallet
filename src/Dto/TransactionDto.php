<?php

declare(strict_types=1);

namespace Bavix\Wallet\Dto;

class TransactionDto
{
    private string $type;

    private int $walletId;

    private string $uuid;

    private bool $confirmed;

    private int $amount;

    private ?array $meta;

    public function __construct(
        string $type,
        int $walletId,
        string $uuid,
        bool $confirmed,
        int $amount,
        ?array $meta
    ) {
        $this->type = $type;
        $this->walletId = $walletId;
        $this->uuid = $uuid;
        $this->confirmed = $confirmed;
        $this->amount = $amount;
        $this->meta = $meta;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }
}
