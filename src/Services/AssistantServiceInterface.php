<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;

interface AssistantServiceInterface
{
    /**
     * @param non-empty-array<TransactionDtoInterface|TransferDtoInterface> $objects
     *
     * @return non-empty-array<int|string, string>
     */
    public function getUuids(array $objects): array;

    /**
     * @param non-empty-array<TransactionDtoInterface> $transactions
     *
     * @return array<int, string>
     */
    public function getSums(array $transactions): array;
}
