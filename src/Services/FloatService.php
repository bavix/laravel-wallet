<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\CastInterface;
use Bavix\Wallet\Contracts\MathInterface;
use Bavix\Wallet\Interfaces\Wallet;

class FloatService
{
    private CastInterface $castService;

    private MathInterface $mathService;

    public function __construct(
        CastInterface $castService,
        MathInterface $mathService
    ) {
        $this->castService = $castService;
        $this->mathService = $mathService;
    }

    public function exponent(Wallet $object): int
    {
        return $this->castService->getWalletModel($object)->decimal_places ?? 2;
    }

    public function signs(Wallet $object): string
    {
        return $this->mathService->pow(10, $this->exponent($object));
    }

    /** @param float|int|string $amount */
    public function intToFloat(Wallet $object, $amount): string
    {
        return $this->mathService->div($amount, $this->signs($object));
    }

    /** @param float|int|string $amount */
    public function floatToInt(Wallet $object, $amount): string
    {
        return $this->mathService->round(
            $this->mathService->mul($amount, $this->signs($object))
        );
    }
}
