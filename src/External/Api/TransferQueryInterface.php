<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;

interface TransferQueryInterface
{
    public function getFrom(): Wallet;

    public function getTo(): Wallet;

    public function getAmount(): float|int|string;

    /**
     * @return array<mixed>|ExtraDtoInterface|null
     */
    public function getMeta(): array|ExtraDtoInterface|null;
}
