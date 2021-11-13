<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\Dto\TransferLazyDto;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\Service\CastService;
use Bavix\Wallet\Internal\TranslatorInterface;

class ConsistencyService implements ConsistencyInterface
{
    private CastService $castService;
    private MathInterface $mathService;
    private TranslatorInterface $translatorService;

    public function __construct(
        TranslatorInterface $translatorService,
        MathInterface $mathService,
        CastService $castService
    ) {
        $this->translatorService = $translatorService;
        $this->mathService = $mathService;
        $this->castService = $castService;
    }

    /**
     * @param float|int|string $amount
     *
     * @throws AmountInvalid
     */
    public function checkPositive($amount): void
    {
        if ($this->mathService->compare($amount, 0) === -1) {
            throw new AmountInvalid(
                $this->translatorService->get('wallet::errors.price_positive'),
                ExceptionInterface::AMOUNT_INVALID
            );
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

        if (($this->mathService->compare($amount, 0) !== 0) && !$wallet->getBalanceAttribute()) {
            throw new BalanceIsEmpty(
                $this->translatorService->get('wallet::errors.wallet_empty'),
                ExceptionInterface::BALANCE_IS_EMPTY
            );
        }

        if (!$wallet->canWithdraw($amount, $allowZero)) {
            throw new InsufficientFunds(
                $this->translatorService->get('wallet::errors.insufficient_funds'),
                ExceptionInterface::INSUFFICIENT_FUNDS
            );
        }
    }

    /**
     * @param TransferLazyDto[] $objects
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function checkTransfer(array $objects): void
    {
        $wallets = [];
        $totalAmount = [];
        foreach ($objects as $object) {
            $withdrawDto = $object->getWithdrawDto();
            $wallet = $this->castService->getWallet($object->getFromWallet(), false);
            $wallets[] = $wallet;

            $totalAmount[$wallet->uuid] = $this->mathService->add(
                ($totalAmount[$wallet->uuid] ?? 0),
                $this->mathService->negative($withdrawDto->getAmount())
            );
        }

        foreach ($wallets as $wallet) {
            $this->checkPotential($wallet, $totalAmount[$wallet->uuid] ?? -1);
        }
    }
}
