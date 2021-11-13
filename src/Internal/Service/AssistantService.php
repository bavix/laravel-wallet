<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\AssistantInterface;
use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Internal\MathInterface;

final class AssistantService implements AssistantInterface
{
    private MathInterface $mathService;

    public function __construct(MathInterface $mathService)
    {
        $this->mathService = $mathService;
    }

    /**
     * @param non-empty-array<TransactionDto|TransferDto> $objects
     *
     * @return non-empty-array<int|string, string>
     */
    public function getUuids(array $objects): array
    {
        return array_map(static fn ($object): string => $object->getUuid(), $objects);
    }

    /**
     * @param non-empty-array<TransactionDto> $transactions
     *
     * @return array<int, string>
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

        return array_filter(
            $amounts,
            fn (string $amount): bool => $this->mathService->compare($amount, 0) !== 0
        );
    }
}
