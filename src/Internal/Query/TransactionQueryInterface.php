<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Query;

interface TransactionQueryInterface
{
    /**
     * @return non-empty-array<int|string, string>
     */
    public function getUuids(): array;
}
