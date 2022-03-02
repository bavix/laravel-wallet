<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Dto\TransferLazyDtoInterface;

interface ConsistencyServiceInterface
{
    /**
     * @param float|int|string $amount
     *
     * @throws AmountInvalid
     */
    public function checkPositive($amount): void;

    /**
     * @param float|int|string $amount
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function checkPotential(Wallet $object, $amount, bool $allowZero = false): void;

    /**
     * @param float|int|string $balance
     * @param float|int|string $amount
     */
    public function canWithdraw($balance, $amount, bool $allowZero = false): bool;

    /**
     * @param TransferLazyDtoInterface[] $objects
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function checkTransfer(array $objects): void;
}
