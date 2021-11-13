<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Contracts\TaxMinimalInterface;

/**
 * @deprecated Will be removed in version 7.1
 * @see TaxMinimalInterface
 */
interface MinimalTaxable extends TaxMinimalInterface, Taxable
{
}
