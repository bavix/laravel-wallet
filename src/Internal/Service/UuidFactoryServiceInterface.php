<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface UuidFactoryServiceInterface
{
    /**
     * Generate a version 4 UUID.
     *
     * Version 4 UUIDs are randomly generated and therefore do not contain any information
     * identifying the originator of the UUID, the generating system, or the time of generation.
     *
     * @see https://en.wikipedia.org/wiki/Universally_unique_identifier#Version_4_(random)
     *
     * @return string The generated version 4 UUID.
     *
     * @throws \Ramsey\Uuid\Exception\InvalidArgumentException If a field is invalid in the UUID.
     * @throws \Ramsey\Uuid\Exception\InvalidUuidStringException If the string we are parsing is not a valid UUID.
     * @throws \Ramsey\Uuid\Exception\UnsupportedOperationException If the UUID implementation can't support a feature.
     * @throws \Ramsey\Uuid\Exception\UuidExceptionInterface If there is an error generating the UUID.
     */
    public function uuid4(): string;
}
