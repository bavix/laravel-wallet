<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Internal\Query\TransferQuery;
use Bavix\Wallet\Internal\Transform\TransferDtoTransformer;
use Bavix\Wallet\Models\Transfer;

class TransferRepository
{
    private TransferDtoTransformer $transformer;

    private Transfer $transfer;

    public function __construct(
        TransferDtoTransformer $transformer,
        Transfer $transfer
    ) {
        $this->transformer = $transformer;
        $this->transfer = $transfer;
    }

    /**
     * @param non-empty-array<int|string, TransferDto> $objects
     */
    public function insert(array $objects): void
    {
        $values = array_map(fn (TransferDto $dto): array => $this->transformer->extract($dto), $objects);
        $this->transfer->newQuery()->insert($values);
    }

    /** @return Transfer[] */
    public function findBy(TransferQuery $query): array
    {
        return $this->transfer->newQuery()
            ->where('uuid', $query->getUuids())
            ->get()
            ->all()
        ;
    }
}
