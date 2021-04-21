<?php

declare(strict_types=1);

namespace Bavix\Wallet\Dto;

class TransferDto
{
    private TransactionDto $from;

    private TransactionDto $to;

    private ?array $meta;

    public function __construct(
        TransactionDto $from,
        TransactionDto $to,
        ?array $meta
    ) {
        $this->from = $from;
        $this->to = $to;
        $this->meta = $meta;
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
