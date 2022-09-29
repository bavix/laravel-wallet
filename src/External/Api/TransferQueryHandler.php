<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\AssistantServiceInterface;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\TransferServiceInterface;

/**
 * @internal
 */
final class TransferQueryHandler implements TransferQueryHandlerInterface
{
    public function __construct(
        private AssistantServiceInterface $assistantService,
        private TransferServiceInterface $transferService,
        private PrepareServiceInterface $prepareService,
        private AtomicServiceInterface $atomicService
    ) {
    }

    public function apply(array $objects): array
    {
        $wallets = $this->assistantService->getWallets(
            array_map(static fn (TransferQuery $query): Wallet => $query->getFrom(), $objects),
        );

        $values = array_map(
            fn (TransferQuery $query) => $this->prepareService->transferLazy(
                $query->getFrom(),
                $query->getTo(),
                Transfer::STATUS_TRANSFER,
                $query->getAmount(),
                $query->getMeta(),
            ),
            $objects
        );

        return $this->atomicService->blocks($wallets, fn () => $this->transferService->apply($values));
    }
}
