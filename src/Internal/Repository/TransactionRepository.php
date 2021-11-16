<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Query\TransactionQueryInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Bavix\Wallet\Models\Transaction;

final class TransactionRepository implements TransactionRepositoryInterface
{
    private TransactionDtoTransformerInterface $transformer;

    private Transaction $transaction;

    public function __construct(
        TransactionDtoTransformerInterface $transformer,
        Transaction $transaction
    ) {
        $this->transformer = $transformer;
        $this->transaction = $transaction;
    }

    /**
     * @param non-empty-array<int|string, TransactionDtoInterface> $objects
     */
    public function insert(array $objects): void
    {
        $values = array_map(fn (TransactionDtoInterface $dto): array => $this->transformer->extract($dto), $objects);
        $this->transaction->newQuery()->insert($values);
    }

    /** @return Transaction[] */
    public function findBy(TransactionQueryInterface $query): array
    {
        return $this->transaction->newQuery()
            ->whereIn('uuid', $query->getUuids())
            ->get()
            ->all()
        ;
    }
}
