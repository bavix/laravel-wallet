<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Observers;

use Bavix\Wallet\Exceptions\UnconfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Illuminate\Database\RecordsNotFoundException;

final class TransferObserver
{
    public function __construct(
        private readonly AtomicServiceInterface $atomicService
    ) {
    }

    /**
     * @throws UnconfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function deleting(Transfer $model): bool
    {
        return $this->atomicService->blocks([$model->from, $model->to], function () use ($model) {
            return $model->from->resetConfirm($model->withdraw)
                && $model->to->resetConfirm($model->deposit);
        });
    }
}
