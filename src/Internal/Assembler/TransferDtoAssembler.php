<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransferDto;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class TransferDtoAssembler implements TransferDtoAssemblerInterface
{
    public function __construct(
        private UuidFactoryServiceInterface $uuidService,
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
        return new TransferDto(
            $uuid ?? $this->uuidService->uuid4(),
            $depositId,
            $withdrawId,
            $status,
            $fromModel->getKey(),
            $toModel->getKey(),
            $discount,
            $fee,
            $extra,
            $this->clockService->now(),
            $this->clockService->now(),
        );
    }
}
