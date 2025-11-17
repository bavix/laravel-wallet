<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Models\Wallet;

/**
 * @api
 */
interface RegulatorServiceInterface
{
    public function forget(Wallet $wallet): bool;

    /**
     * @return non-empty-string
     */
    public function diff(Wallet $wallet): string;

    /**
     * @return non-empty-string
     */
    public function amount(Wallet $wallet): string;

    /**
     * @param float|int|non-empty-string $value
     */
    public function sync(Wallet $wallet, float|int|string $value): bool;

    /**
     * @param float|int|non-empty-string $value
     * @return non-empty-string
     */
    public function increase(Wallet $wallet, float|int|string $value): string;

    public function decrease(Wallet $wallet, float|int|string $value): string;

    public function committing(): void;

    public function committed(): void;

    public function purge(): void;
}
