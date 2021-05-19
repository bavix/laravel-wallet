<?php

declare(strict_types=1);

namespace Bavix\Wallet\Assemblers;

use Bavix\Wallet\Dto\BasketDto;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Services\UuidFactoryService;

class BasketDtoAssembler
{
    private UuidFactoryService $uuidFactoryService;

    public function __construct(UuidFactoryService $uuidFactoryService)
    {
        $this->uuidFactoryService = $uuidFactoryService;
    }

    /** @param Product[] $items */
    public function create(array $items, ?array $meta): BasketDto
    {
        return new BasketDto(
            $this->uuidFactoryService->uuid4(),
            $items,
            $meta
        );
    }
}
