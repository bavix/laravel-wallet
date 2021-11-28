<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Models\Wallet;

interface StateServiceInterface
{
    public function persist(Wallet $wallet): void;

    public function commit(): void;

    public function purge(): void;
}
