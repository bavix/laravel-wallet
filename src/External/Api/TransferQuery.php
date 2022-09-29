<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;

final class TransferQuery
{
    /**
     * @param array<mixed>|ExtraDtoInterface|null $meta
     */
    public function __construct(
        private Wallet $from,
        private Wallet $to,
        private float|int|string $amount,
        private array|ExtraDtoInterface|null $meta
    ) {
    }

    public function getFrom(): Wallet
    {
        return $this->from;
    }

    public function getTo(): Wallet
    {
        return $this->to;
    }

    public function getAmount(): float|int|string
    {
        return $this->amount;
    }

    /**
     * @return array<mixed>|ExtraDtoInterface|null
     */
    public function getMeta(): array|ExtraDtoInterface|null
    {
        return $this->meta;
    }
}
