<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\RecordsNotFoundException;

/** @deprecated */
final class WalletServiceLegacy
{
    private MathServiceInterface $mathService;
    private RegulatorServiceInterface $regulatorService;
    private AtomicServiceInterface $atomicService;
    private StateServiceInterface $stateService;

    public function __construct(
        MathServiceInterface $mathService,
        RegulatorServiceInterface $regulatorService,
        AtomicServiceInterface $atomicService,
        StateServiceInterface $stateService
    ) {
        $this->mathService = $mathService;
        $this->regulatorService = $regulatorService;
        $this->atomicService = $atomicService;
        $this->stateService = $stateService;
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @see WalletModel::refreshBalance()
     * @deprecated
     */
    public function refresh(WalletModel $wallet): bool
    {
        return $this->atomicService->block($wallet, function () use ($wallet) {
            $whatIs = $wallet->balance;
            $balance = $wallet->getAvailableBalance();
            if ($this->mathService->compare($whatIs, $balance) === 0) {
                return true;
            }

            $this->stateService->persist($wallet);
            $wallet->fill(['balance' => $balance])->syncOriginalAttribute('balance');

            return $this->regulatorService->sync($wallet, $balance);
        });
    }
}
