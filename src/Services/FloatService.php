<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Mathable;

class FloatService
{
    /** @var Mathable */
    protected $math;

    public function __construct(
        Mathable $math
    ) {
        $this->math = $math;
    }

    public function balanceIntToFloat(string $balance, int $decimalPlaces): string
    {
        return $this->math->div($balance, $decimalPlaces);
    }

    public function balanceFloatToInt(string $balance, int $decimalPlaces): string
    {
        return $this->math->round(
            $this->math->mul($balance, $decimalPlaces)
        );
    }
}
