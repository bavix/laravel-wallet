<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Values;

final class Money
{
    public function __construct(
        public readonly string $amount,
        public readonly string $currency,
    ) {
    }
}
