<?php

declare(strict_types=1);

namespace Bavix\Wallet\Commands;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CastServiceInterface;
use Illuminate\Console\Command;

final class TransferFixCommand extends Command
{
    protected $signature = 'bx:transfer:fix';

    protected $description = 'Brings transfers to the correct form/to.';

    public function handle(Wallet $wallet, Transfer $transfer, CastServiceInterface $castService): void
    {
        $query = $transfer::with(['from', 'to'])
            ->orWhere('from_type', '<>', $wallet->getMorphClass())
            ->orWhere('to_type', '<>', $wallet->getMorphClass())
        ;

        $query->each(fn (Transfer $object) => $this->fix($castService, $wallet, $object));
    }

    private function fix(CastServiceInterface $castService, Wallet $wallet, Transfer $transfer): void
    {
        if ($transfer->from_type !== $wallet->getMorphClass()) {
            $fromWallet = $castService->getWallet($transfer->from);
            $transfer->from_type = $fromWallet->getMorphClass();
            $transfer->from_id = $fromWallet->getKey();
        }

        if ($transfer->to_type !== $wallet->getMorphClass()) {
            $toWallet = $castService->getWallet($transfer->to);
            $transfer->to_type = $toWallet->getMorphClass();
            $transfer->to_id = $toWallet->getKey();
        }

        if ($transfer->isDirty()) {
            $transfer->save();
        }
    }
}
