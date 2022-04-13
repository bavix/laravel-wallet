<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Exceptions;

use Bavix\Wallet\Internal\Exceptions\RuntimeExceptionInterface;
use RuntimeException;

final class UnknownEventException extends RuntimeException implements RuntimeExceptionInterface
{
}
