<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal;

interface UuidInterface
{
    public function uuid4(): string;
}
