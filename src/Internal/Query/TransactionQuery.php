<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Query;

/**
 * @psalm-immutable
 * @internal
 */
final class TransactionQuery implements TransactionQueryInterface
{
    /**
     * @param non-empty-array<int|string, string> $uuids
     */
    public function __construct(
        private array $uuids
    ) {
    }

    /**
     * @return non-empty-array<int|string, string>
     */
    public function getUuids(): array
    {
        return $this->uuids;
    }
}
