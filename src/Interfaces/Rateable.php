<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Rateable
{
    public function withAmount(string $amount): self;

    public function withCurrency(Wallet $wallet): self;

    public function convertTo(Wallet $wallet): string;
}
