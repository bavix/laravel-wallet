<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface UuidFactoryServiceInterface
{
    public function uuid4(): string;
}
