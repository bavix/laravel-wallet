<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Query\TransferQuery;
use Bavix\Wallet\Internal\Query\TransferQueryInterface;

final class TransferQueryAssembler implements TransferQueryAssemblerInterface
{
    /** @param non-empty-array<int|string, string> $uuids */
    public function create(array $uuids): TransferQueryInterface
    {
        return new TransferQuery($uuids);
    }
}
