<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

/**
 * @psalm-internal
 */
final class JsonService
{
    public function encode(?array $data): ?string
    {
        return $data === null ? null : json_encode($data);
    }
}
