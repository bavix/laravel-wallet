<?php

declare(strict_types=1);

namespace Bavix\Wallet\Exceptions;

use InvalidArgumentException;

final class WalletOwnerInvalid extends InvalidArgumentException implements InvalidArgumentExceptionInterface
{
}
