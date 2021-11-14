<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

interface AtomicKeyServiceInterface
{
    public function getIdentifier(object $object): string;
}
