<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Services\AssistantServiceInterface;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\TransactionServiceInterface;

/**
 * @internal
 */
final class TransactionQueryHandler implements TransactionQueryHandlerInterface
{
    public function __construct(
        private readonly TransactionServiceInterface $transactionService,
        private readonly AssistantServiceInterface $assistantService,
        private readonly PrepareServiceInterface $prepareService,
        private readonly AtomicServiceInterface $atomicService
    ) {
    }

    public function apply(array $objects): array
    {
        $wallets = $this->assistantService->getWallets(
            array_map(static fn (TransactionQuery $query): Wallet => $query->getWallet(), $objects),
        );

        $values = array_map(
            fn (TransactionQuery $query) => match ($query->getType()) {
                TransactionQuery::TYPE_DEPOSIT => $this->prepareService->deposit(
                    $query->getWallet(),
                    $query->getAmount(),
                    $query->getMeta(),
                    $query->isConfirmed(),
                    $query->getUuid(),
                ),
                TransactionQuery::TYPE_WITHDRAW => $this->prepareService->withdraw(
                    $query->getWallet(),
                    $query->getAmount(),
                    $query->getMeta(),
                    $query->isConfirmed(),
                    $query->getUuid(),
                )
            },
            $objects
        );

        return $this->atomicService->blocks(
            $wallets,
            fn () => $this->transactionService->apply($wallets, $values),
        );
    }
}
