<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Models\Wallet;

interface BookkeeperServiceInterface
{
    public function missing(Wallet $wallet): bool;

    public function amount(Wallet $wallet): string;

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function sync(Wallet $wallet, float|int|string $value): bool;

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function increase(Wallet $wallet, float|int|string $value): string;

    /**
     * @param non-empty-array<string, Wallet> $wallets
     *
     * @return array<string, string>
     */
    public function multiAmount(array $wallets): array;

    /**
     * @param non-empty-array<string, float|int|string> $balances
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function multiSync(array $balances): bool;

    /**
     * @param non-empty-array<string, Wallet> $wallets
     * @param non-empty-array<string, float|int|string> $incrementValues
     *
     * @return  non-empty-array<string, string>
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function multiIncrease(array $wallets, array $incrementValues): array;
}
