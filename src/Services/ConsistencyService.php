<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\Service\CastService;

class ConsistencyService implements ConsistencyInterface
{
    private CastService $castService;

    private MathInterface $math;

    public function __construct(
        MathInterface $math,
        CastService $castService
    ) {
        $this->math = $math;
        $this->castService = $castService;
    }

    /**
     * @param float|int|string $amount
     *
     * @throws AmountInvalid
     */
    public function checkPositive($amount): void
    {
        if ($this->math->compare($amount, 0) === -1) {
            throw new AmountInvalid(trans('wallet::errors.price_positive'));
        }
    }

    /**
     * @param float|int|string $amount
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function checkPotential(Wallet $object, $amount, bool $allowZero = false): void
    {
        $wallet = $this->castService->getWallet($object, false);

        if ($amount && !$wallet->balance) {
            throw new BalanceIsEmpty(trans('wallet::errors.wallet_empty'));
        }

        if (!$wallet->canWithdraw($amount, $allowZero)) {
            throw new InsufficientFunds(trans('wallet::errors.insufficient_funds'));
        }
    }
}
