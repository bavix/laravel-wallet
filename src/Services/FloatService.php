<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Mathable;

class FloatService
{
    protected Mathable $mathService;

    public function __construct(
        Mathable $math
    ) {
        $this->mathService = $math;
    }

    public function balanceIntToFloat(string $balance, int $decimalPlaces): string
    {
        return $this->mathService->div($balance, (string) $decimalPlaces);
    }

    public function balanceFloatToInt(string $balance, int $decimalPlaces): string
    {
        return $this->mathService->round(
            $this->mathService->mul($balance, (string) $decimalPlaces)
        );
    }
}
