<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use DateTimeImmutable;

/**
 * @see https://github.com/php-fig/fig-standards/blob/master/proposed/clock.md
 */
interface ClockServiceInterface
{
    public function now(): DateTimeImmutable;
}
