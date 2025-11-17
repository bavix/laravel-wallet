<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Internal\Service\IdentifierFactoryServiceInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class TransactionDtoAssembler implements TransactionDtoAssemblerInterface
{
    public function __construct(
        private IdentifierFactoryServiceInterface $identifierFactoryService,
        private ClockServiceInterface $clockService,
    ) {
    }

    public function create(
        Model $payable,
        int $walletId,
        string $type,
        float|int|string $amount,
        bool $confirmed,
        ?array $meta,
        ?string $uuid
    ): TransactionDtoInterface {
        /** @var int|string $payableId */
        $payableId = $payable->getKey();

        return new TransactionDto(
            $uuid ?? $this->identifierFactoryService->generate(),
            $payable->getMorphClass(),
            $payableId,
            $walletId,
            $type,
            $amount,
            $confirmed,
            $meta,
            $this->clockService->now(),
            $this->clockService->now(),
        );
    }
}
