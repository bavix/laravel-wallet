<?php

declare(strict_types=1);

namespace Bavix\Wallet\Exceptions;

use Bavix\Wallet\Internal\Exceptions\LogicExceptionInterface;
use LogicException;

class ProductEnded extends LogicException implements LogicExceptionInterface
{
}
