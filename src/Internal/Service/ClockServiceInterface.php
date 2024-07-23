<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use DateTimeImmutable;

/**
 * Interface for a clock service.
 *
 * This interface provides a way to get the current datetime immutably.
 */
interface ClockServiceInterface
{
    /**
     * Returns a new DateTimeImmutable object representing the current date and time.
     *
     * This method is compliant with the Clock interface from the PHP-FIG proposed standard
     * for a standardized way to get the current date and time.
     *
     * @see https://www.php-fig.org/psr/psr-20/ PSR-20: Clock Interface
     *
     * @return DateTimeImmutable The current date and time immutably.
     *
     * @psalm-immutable
     */
    public function now(): DateTimeImmutable;
}
