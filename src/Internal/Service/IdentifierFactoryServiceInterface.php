<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
use Ramsey\Uuid\Exception\UuidExceptionInterface;

interface IdentifierFactoryServiceInterface
{
    /**
     * Generate a unique identifier string.
     *
     * This method generates a unique identifier string using internal algorithm.
     *
     * @return non-empty-string The generated ID string.
     *
     * @throws InvalidArgumentException If a field is invalid in the UUID.
     * @throws InvalidUuidStringException If the string we are parsing is not a valid UUID.
     * @throws UnsupportedOperationException If the UUID implementation can't support a feature.
     * @throws UuidExceptionInterface If there is an error generating the UUID.
     */
    public function generate(): string;
}
