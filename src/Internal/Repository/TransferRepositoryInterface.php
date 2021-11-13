<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Internal\Query\TransferQuery;
use Bavix\Wallet\Models\Transfer;

interface TransferRepositoryInterface
{
    /**
     * @param non-empty-array<int|string, TransferDto> $objects
     */
    public function insert(array $objects): void;

    /** @return Transfer[] */
    public function findBy(TransferQuery $query): array;
}
