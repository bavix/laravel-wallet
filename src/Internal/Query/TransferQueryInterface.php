<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Query;

interface TransferQueryInterface
{
    /**
     * Returns an array of UUIDs for the transfers.
     *
     * The array should not be empty and should contain only non-empty-strings or integers.
     *
     * @return non-empty-array<int|string, string> An array of transfer UUIDs.
     */
    public function getUuids(): array;
}
