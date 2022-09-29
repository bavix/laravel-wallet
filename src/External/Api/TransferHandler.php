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
final class TransferHandler implements TransferHandlerInterface
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
        $wallets = $this->assistantService->getUniqueWallets(
            array_map(static fn (array $object): Wallet => $object['from'], $objects),
        );

        $values = array_map(
            fn (array $object) => $this->prepareService->transferLazy(
                $object['from'],
                $object['to'],
                Transfer::STATUS_TRANSFER,
                $object['amount'],
                $object['meta'] ?? null,
            ),
            $objects
        );

        return $this->atomicService->blocks($wallets, fn () => $this->transferService->apply($values));
    }
}
