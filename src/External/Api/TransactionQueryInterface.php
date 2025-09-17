<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Enums\TransactionType;
use Bavix\Wallet\Interfaces\Wallet;

interface TransactionQueryInterface
{
    public function getType(): TransactionType;

    public function getWallet(): Wallet;

    public function getAmount(): float|int|string;

    /**
     * @return array<mixed>|null
     */
    public function getMeta(): ?array;

    public function isConfirmed(): bool;

    public function getUuid(): ?string;
}
