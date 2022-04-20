<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Repository\TransferRepositoryInterface;

final class TransferService implements TransferServiceInterface
{
    public function __construct(private TransferRepositoryInterface $repository)
    {
    }

    /**
     * @param int[] $ids
     */
    public function updateStatusByIds(string $status, array $ids): bool
    {
        return count($ids) !== 0 && count($ids) === $this->repository->updateStatusByIds($status, $ids);
    }
}
