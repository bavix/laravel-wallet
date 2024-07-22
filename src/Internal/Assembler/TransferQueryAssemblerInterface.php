<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Query\TransferQueryInterface;

interface TransferQueryAssemblerInterface
{
    /**
     * Create a new TransferQuery object with the given UUIDs.
     *
     * @param non-empty-array<int|string, string> $uuids The UUIDs of the transfers.
     * @return TransferQueryInterface The newly created TransferQuery object.
     */
    public function create(array $uuids): TransferQueryInterface;
}
