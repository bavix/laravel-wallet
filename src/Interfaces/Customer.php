<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Contracts\CustomerInterface;

/**
 * @deprecated Will be removed in version 7.1
 * @see CustomerInterface
 */
interface Customer extends Wallet, CustomerInterface
{
}
