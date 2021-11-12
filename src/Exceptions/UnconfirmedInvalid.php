<?php

declare(strict_types=1);

namespace Bavix\Wallet\Exceptions;

use Bavix\Wallet\Internal\Exceptions\InvalidArgumentExceptionInterface;
use InvalidArgumentException;

final class UnconfirmedInvalid extends InvalidArgumentException implements InvalidArgumentExceptionInterface
{
}
