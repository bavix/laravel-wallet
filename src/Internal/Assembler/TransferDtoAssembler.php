<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Internal\Service\IdentifierFactoryServiceInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class TransferDtoAssembler implements TransferDtoAssemblerInterface
{
    public function __construct(
        private IdentifierFactoryServiceInterface $identifierFactoryService,
        private ClockServiceInterface $clockService,
    ) {
    }

    /**
     * @param array<mixed>|null $extra
     */
    public function create(
        int $depositId,
        int $withdrawId,
        string $status,
        Model $fromModel,
        Model $toModel,
        int $discount,
        string $fee,
        ?string $uuid,
        ?array $extra,
    ): TransferDtoInterface {
        // Wallet models always have int keys
        /** @var int $fromId */
        $fromId = $fromModel->getKey();

        /** @var int $toId */
        $toId = $toModel->getKey();

        return new TransferDto(
            $uuid ?? $this->identifierFactoryService->generate(),
            $depositId,
            $withdrawId,
            $status,
            $fromId,
            $toId,
            $discount,
            $fee,
            $extra,
            $this->clockService->now(),
            $this->clockService->now(),
        );
    }
}
