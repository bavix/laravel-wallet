<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Throwable;

/**
 * @internal
 */
final class JsonService implements JsonServiceInterface
{
    public function encode(?array $data): ?string
    {
        try {
            return $data === null ? null : json_encode($data, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }
    }
}
