<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
use Ramsey\Uuid\Exception\UuidExceptionInterface;
use Ramsey\Uuid\UuidFactory;

final readonly class IdentifierFactoryService implements IdentifierFactoryServiceInterface
{
    /**
     * @param UuidFactory $uuidFactory Service for generating UUIDs.
     * @param ClockServiceInterface $clockService Service for getting the current time.
     */
    public function __construct(
        private UuidFactory $uuidFactory,
        private ClockServiceInterface $clockService,
    ) {
    }

    /**
     * Generate a ID string using the uuid7 algorithm.
     *
     * uuid7 is a time-based UUID algorithm that uses the current time in milliseconds,
     * combined with a random number to generate a unique ID.
     *
     * @return non-empty-string The generated ID string.
     *
     * @throws InvalidArgumentException If a field is invalid in the UUID.
     * @throws InvalidUuidStringException If the string we are parsing is not a valid UUID.
     * @throws UnsupportedOperationException If the UUID implementation can't support a feature.
     * @throws UuidExceptionInterface If there is an error generating the UUID.
     */
    public function generate(): string
    {
        return $this->uuidFactory->uuid7($this->clockService->now())
            ->toString();
    }
}
