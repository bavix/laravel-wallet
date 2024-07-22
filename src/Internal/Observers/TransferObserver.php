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

final readonly class TransferObserver
{
    public function __construct(
        private AtomicServiceInterface $atomicService
    ) {
    }

    /**
     * Handle the deleting event for the Transfer model.
     *
     * This method is called when a transfer is being deleted.
     * It safely resets the confirmation of the transfer.
     *
     * @param Transfer $model The transfer model being deleted.
     * @return bool Returns true if the confirmation was reset, false otherwise.
     *
     * @throws UnconfirmedInvalid If the transfer is not confirmed.
     * @throws WalletOwnerInvalid If the transfer does not belong to the wallet.
     * @throws RecordNotFoundException If the transfer was not found.
     * @throws RecordsNotFoundException If no transfers were found.
     * @throws TransactionFailedException If the transfer failed.
     * @throws ExceptionInterface If an exception occurred.
     */
    public function deleting(Transfer $model): bool
    {
        // Reset confirmation.
        // This method removes the confirmation of the transfer only if it is already confirmed.
        // If the transfer does not belong to the wallet, a WalletOwnerInvalid exception will be thrown.
        // If the transfer was not found, a RecordNotFoundException will be thrown.
        // Block both the wallet of the user who is sending the money and the wallet of the user who is receiving the money.
        return $this->atomicService->blocks([$model->from, $model->to], function () use ($model) {
            // Reset confirmation of the transfer for the sender's wallet.
            // Returns true if the confirmation was reset, false otherwise.
            return $model->from->safeResetConfirm($model->withdraw)
                // Reset confirmation of the transfer for the receiver's wallet.
                // Returns true if the confirmation was reset, false otherwise.
                && $model->to->safeResetConfirm($model->deposit);
        });
    }
}
