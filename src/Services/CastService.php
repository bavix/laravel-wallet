<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\WalletCreatedEventAssemblerInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\DispatcherServiceInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Model;

/** @psalm-internal */
final class CastService implements CastServiceInterface
{
    private WalletCreatedEventAssemblerInterface $walletCreatedEventAssembler;
    private DispatcherServiceInterface $dispatcherService;
    private DatabaseServiceInterface $databaseService;

    public function __construct(
        WalletCreatedEventAssemblerInterface $walletCreatedEventAssembler,
        DispatcherServiceInterface $dispatcherService,
        DatabaseServiceInterface $databaseService
    ) {
        $this->walletCreatedEventAssembler = $walletCreatedEventAssembler;
        $this->dispatcherService = $dispatcherService;
        $this->databaseService = $databaseService;
    }

    /** @throws ExceptionInterface */
    public function getWallet(Wallet $object, bool $save = true): WalletModel
    {
        $wallet = $this->getModel($object);
        if (!($wallet instanceof WalletModel)) {
            $wallet = $wallet->getAttribute('wallet');
            assert($wallet instanceof WalletModel);
        }

        if ($save && !$wallet->exists) {
            $this->databaseService->transaction(function () use ($wallet) {
                $result = $wallet::withoutEvents(fn () => $wallet->save());
                $this->dispatcherService->dispatch($this->walletCreatedEventAssembler->create($wallet));

                return $result;
            });
        }

        return $wallet;
    }

    /** @param Model|Wallet $object */
    public function getHolder($object): Model
    {
        return $this->getModel($object instanceof WalletModel ? $object->holder : $object);
    }

    public function getModel(object $object): Model
    {
        assert($object instanceof Model);

        return $object;
    }
}
