<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Exceptions;

use LogicException;

final class TransactionStartException extends LogicException implements LogicExceptionInterface
{
}
