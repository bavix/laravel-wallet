<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Internal\Query\TransferQueryInterface;
use Bavix\Wallet\Models\Transfer;

interface TransferRepositoryInterface
{
    /**
     * @param non-empty-array<int|string, TransferDtoInterface> $objects
     */
    public function insert(array $objects): void;

    /** @return Transfer[] */
    public function findBy(TransferQueryInterface $query): array;
}
