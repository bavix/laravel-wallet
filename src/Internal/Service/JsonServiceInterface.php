<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface JsonServiceInterface
{
    /**
     * @param array<mixed>|null $data
     */
    public function encode(?array $data): ?string;
}
