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
     * @param TransferDto[] $transfers
     *
     * @return Transfer[]
     */
    public function insert(array $transfers): array
    {
        $values = array_map(fn (TransferDto $dto): array => $this->transformer->extract($dto), $transfers);
        $this->transfer->newQuery()->insert($values);

        $uuids = array_map(static fn (TransferDto $dto): string => $dto->getUuid(), $transfers);
        $query = new TransferQuery($uuids);

        return $this->findBy($query);
    }

    /** @return Transfer[] */
    public function findBy(TransferQuery $transferQuery): array
    {
        return $this->transfer->newQuery()
            ->where('uuid', $transferQuery->getUuids())
            ->get()
            ->all()
        ;
    }
}
