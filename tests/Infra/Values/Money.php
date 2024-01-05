<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Values;

final readonly class Money
{
    public function __construct(
        public string $amount,
        public string $currency,
    ) {
    }
}
