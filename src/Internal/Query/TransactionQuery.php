<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Query;

/**
 * @immutable
 * @internal
 */
final readonly class TransactionQuery implements TransactionQueryInterface
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
