<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Exceptions;

use InvalidArgumentException;

final class CartEmptyException extends InvalidArgumentException implements InvalidArgumentExceptionInterface
{
}
