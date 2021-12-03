<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Models\Wallet;

interface RegulatorServiceInterface
{
    public function missing(Wallet $wallet): bool;

    public function diff(Wallet $wallet): string;

    public function amount(Wallet $wallet): string;

    /** @param float|int|string $value */
    public function sync(Wallet $wallet, $value): bool;

    /** @param float|int|string $value */
    public function increase(Wallet $wallet, $value): string;

    /** @param float|int|string $value */
    public function decrease(Wallet $wallet, $value): string;

    public function approve(): void;

    public function purge(): void;
}
