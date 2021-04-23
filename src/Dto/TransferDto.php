<?php

declare(strict_types=1);

namespace Bavix\Wallet\Dto;

class TransferDto
{
    private string $idempotencyKey;

    private TransactionDto $from;

    private TransactionDto $to;

    private ?array $meta;

    public function __construct(
        string $idempotencyKey,
        TransactionDto $from,
        TransactionDto $to,
        ?array $meta
    ) {
        $this->idempotencyKey = $idempotencyKey;
        $this->from = $from;
        $this->to = $to;
        $this->meta = $meta;
    }

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
    }

    public function getFrom(): TransactionDto
    {
        return $this->from;
    }

    public function getTo(): TransactionDto
    {
        return $this->to;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }
}
