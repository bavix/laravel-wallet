<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Illuminate\Database\Eloquent\Model;

final class TransactionDtoAssembler implements TransactionDtoAssemblerInterface
{
    private UuidFactoryServiceInterface $uuidService;

    public function __construct(UuidFactoryServiceInterface $uuidService)
    {
        $this->uuidService = $uuidService;
    }

    public function create(
        Model $payable,
        int $walletId,
        string $type,
        string $amount,
        bool $confirmed,
        ?array $meta
    ): TransactionDtoInterface {
        return new TransactionDto(
            $this->uuidService->uuid4(),
            $payable->getMorphClass(),
            $payable->getKey(),
            $walletId,
            $type,
            $amount,
            $confirmed,
            $meta
        );
    }
}
