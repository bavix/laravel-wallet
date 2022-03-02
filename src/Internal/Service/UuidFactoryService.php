<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Ramsey\Uuid\UuidFactory;

final class UuidFactoryService implements UuidFactoryServiceInterface
{
    public function __construct(
        private UuidFactory $uuidFactory
    ) {
    }

    public function uuid4(): string
    {
        return $this->uuidFactory->uuid4()
            ->toString()
        ;
    }
}
