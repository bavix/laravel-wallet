<?php

declare(strict_types=1);

namespace Bavix\Wallet\Exceptions;

use Bavix\Wallet\Internal\Exceptions\LogicExceptionInterface;
use LogicException;

final class InsufficientFunds extends LogicException implements LogicExceptionInterface
{
}
