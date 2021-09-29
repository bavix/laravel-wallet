<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\UuidInterface;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;

class UuidFactoryService implements UuidInterface
{
    private UuidFactoryInterface $uuidFactory;

    public function __construct(UuidFactory $uuidFactory)
    {
        $this->uuidFactory = $uuidFactory;
    }

    public function uuid4(): string
    {
        return $this->uuidFactory->uuid4()->toString();
    }
}
