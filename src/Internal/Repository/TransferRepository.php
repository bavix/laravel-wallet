<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Internal\Query\TransferQuery;
use Bavix\Wallet\Internal\Transform\TransferDtoTransformerInterface;
use Bavix\Wallet\Models\Transfer;

final class TransferRepository implements TransferRepositoryInterface
{
    private TransferDtoTransformerInterface $transformer;

    private Transfer $transfer;

    public function __construct(
        TransferDtoTransformerInterface $transformer,
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
            ->whereIn('uuid', $query->getUuids())
            ->get()
            ->all()
        ;
    }
}
