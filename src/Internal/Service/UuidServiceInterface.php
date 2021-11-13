<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface UuidServiceInterface
{
    public function uuid4(): string;
}
