<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Services\MathService;

class AssistantService
{
    private MathService $mathService;

    public function __construct(MathService $mathService)
    {
        $this->mathService = $mathService;
    }

    /**
     * @param TransactionDto[]|TransferDto[] $objects
     *
     * @return string[]
     */
    public function getUuids(array $objects): array
    {
        return array_map(static fn ($object): string => $object->getUuid(), $objects);
    }

    /**
     * @param TransactionDto[] $transactions
     *
     * @return string[]
     */
    public function getSums(array $transactions): array
    {
        $amounts = [];
        foreach ($transactions as $transaction) {
            if ($transaction->isConfirmed()) {
                $amounts[$transaction->getWalletId()] = $this->mathService->add(
                    $amounts[$transaction->getWalletId()] ?? 0,
                    $transaction->getAmount()
                );
            }
        }

        return $amounts;
    }
}
