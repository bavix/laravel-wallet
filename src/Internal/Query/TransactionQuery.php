<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Query;

/** @psalm-immutable */
final class TransactionQuery implements TransactionQueryInterface
{
    /** @var non-empty-array<int|string, string> */
    private array $uuids;

    /** @param non-empty-array<int|string, string> $uuids */
    public function __construct(array $uuids)
    {
        $this->uuids = $uuids;
    }

    /** @return non-empty-array<int|string, string> */
    public function getUuids(): array
    {
        return $this->uuids;
    }
}
