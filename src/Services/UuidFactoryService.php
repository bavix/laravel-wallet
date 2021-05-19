<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactoryInterface;

class UuidFactoryService
{
    private UuidFactoryInterface $uuidFactory;

    public function __construct()
    {
        $this->uuidFactory = Uuid::getFactory();
    }

    public function uuid4(): string
    {
        return $this->uuidFactory->uuid4()->toString();
    }
}
