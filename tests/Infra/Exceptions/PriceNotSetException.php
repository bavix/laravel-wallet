<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Exceptions;

use Bavix\Wallet\Internal\Exceptions\InvalidArgumentExceptionInterface;
use InvalidArgumentException;

final class PriceNotSetException extends InvalidArgumentException implements InvalidArgumentExceptionInterface
{
}
