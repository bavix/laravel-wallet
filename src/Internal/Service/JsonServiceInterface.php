<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface JsonServiceInterface
{
    public function encode(?array $data): ?string;
}
