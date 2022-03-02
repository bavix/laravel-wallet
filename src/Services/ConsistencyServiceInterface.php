<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Dto\TransferLazyDtoInterface;

interface ConsistencyServiceInterface
{
    /**
     * @param float|int|string $amount
     */
    public function checkPositive($amount): void;

    /**
     * @param float|int|string $amount
     */
    public function checkPotential(Wallet $object, $amount, bool $allowZero = false): void;

    /**
     * @param float|int|string $balance
     * @param float|int|string $amount
     */
    public function canWithdraw($balance, $amount, bool $allowZero = false): bool;

    /**
     * @param TransferLazyDtoInterface[] $objects
     */
    public function checkTransfer(array $objects): void;
}
