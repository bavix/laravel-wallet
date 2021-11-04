<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Query\TransactionQuery;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;
use Bavix\Wallet\Models\Transaction;

class TransactionRepository
{
    private TransactionDtoTransformer $transformer;

    private Transaction $transaction;

    public function __construct(
        TransactionDtoTransformer $transformer,
        Transaction $transaction
    ) {
        $this->transformer = $transformer;
        $this->transaction = $transaction;
    }

    /**
     * @param non-empty-array<int, TransactionDto> $objects
     *
     * @return non-empty-array<int|string, Transaction>
     */
    public function insert(array $objects): array
    {
        $values = array_map(fn (TransactionDto $dto): array => $this->transformer->extract($dto), $objects);
        $this->transaction->newQuery()->insert($values);

        $uuids = array_map(static fn (TransactionDto $dto): string => $dto->getUuid(), $objects);
        $query = new TransactionQuery($uuids);

        return $this->findBy($query);
    }

    /** @return Transaction[] */
    public function findBy(TransactionQuery $query): array
    {
        return $this->transaction->newQuery()
            ->where('uuid', $query->getUuids())
            ->get()
            ->all()
        ;
    }
}
