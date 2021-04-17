<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Storable
{
    public function getBalance(Wallet $object): string;

    public function incBalance(Wallet $object, string $amount);

    public function setBalance(Wallet $object, string $amount): bool;

    public function fresh(): bool;
}
