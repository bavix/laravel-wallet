<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Models\Wallet;

interface RegulatorServiceInterface
{
    public function missing(Wallet $wallet): bool;

    public function diff(Wallet $wallet): string;

    public function amount(Wallet $wallet): string;

    public function sync(Wallet $wallet, float|int|string $value): bool;

    public function increase(Wallet $wallet, float|int|string $value): string;

    public function decrease(Wallet $wallet, float|int|string $value): string;

    public function committing(): void;

    public function committed(): void;

    /**
     * @deprecated
     *
     * @see committing
     * @see committed
     */
    public function approve(): void;

    public function purge(): void;
}
