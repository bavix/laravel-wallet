<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Exceptions;

use UnexpectedValueException;

final class LockProviderNotFoundException extends UnexpectedValueException implements UnexpectedValueExceptionInterface
{
}
