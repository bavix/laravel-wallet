<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;

final class UuidFactoryService implements UuidFactoryServiceInterface
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
