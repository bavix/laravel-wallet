<?php

declare(strict_types=1);

namespace Bavix\Wallet\Settings;

use Illuminate\Config\Repository;

class MathSetting
{
    private int $scale;

    public function __construct(Repository $repository)
    {
        $this->scale = (int) $repository->get('wallet.math.scale', 64);
    }

    public function getScale(): int
    {
        return $this->scale;
    }
}
