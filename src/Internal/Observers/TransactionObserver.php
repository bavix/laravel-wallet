<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Observers;

use Bavix\Wallet\Exceptions\UnconfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transaction;
use Illuminate\Database\RecordsNotFoundException;

final class TransactionObserver
{
    /**
     * @throws UnconfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function deleting(Transaction $model): bool
    {
        return $model->wallet->resetConfirm($model);
    }
}
