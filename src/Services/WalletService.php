<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Assembler\WalletCreatedEventAssemblerInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Repository\WalletRepositoryInterface;
use Bavix\Wallet\Internal\Service\DispatcherServiceInterface;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

final class WalletService implements WalletServiceInterface
{
    private WalletCreatedEventAssemblerInterface $walletCreatedEventAssembler;
    private UuidFactoryServiceInterface $uuidFactoryService;
    private DispatcherServiceInterface $dispatcherService;
    private WalletRepositoryInterface $walletRepository;

    public function __construct(
        WalletCreatedEventAssemblerInterface $walletCreatedEventAssembler,
        UuidFactoryServiceInterface $uuidFactoryService,
        DispatcherServiceInterface $dispatcherService,
        WalletRepositoryInterface $walletRepository
    ) {
        $this->walletCreatedEventAssembler = $walletCreatedEventAssembler;
        $this->uuidFactoryService = $uuidFactoryService;
        $this->dispatcherService = $dispatcherService;
        $this->walletRepository = $walletRepository;
    }

    public function create(Model $model, array $data): Wallet
    {
        $wallet = $this->walletRepository->create(array_merge(
            config('wallet.wallet.creating', []),
            $data,
            [
                'uuid' => $this->uuidFactoryService->uuid4(),
                'holder_type' => $model->getMorphClass(),
                'holder_id' => $model->getKey(),
            ]
        ));

        $event = $this->walletCreatedEventAssembler->create($wallet);
        $this->dispatcherService->dispatch($event);

        return $wallet;
    }

    public function findBySlug(Model $model, string $slug): ?Wallet
    {
        return $this->walletRepository->findBySlug(
            $model->getMorphClass(),
            (int) $model->getKey(),
            $slug
        );
    }

    public function findByUuid(string $uuid): ?Wallet
    {
        return $this->walletRepository->findByUuid($uuid);
    }

    public function findById(int $id): ?Wallet
    {
        return $this->walletRepository->findById($id);
    }

    /** @throws ModelNotFoundException */
    public function getBySlug(Model $model, string $slug): Wallet
    {
        return $this->walletRepository->getBySlug(
            $model->getMorphClass(),
            (int) $model->getKey(),
            $slug
        );
    }

    /** @throws ModelNotFoundException */
    public function getByUuid(string $uuid): Wallet
    {
        return $this->walletRepository->getByUuid($uuid);
    }

    /** @throws ModelNotFoundException */
    public function getById(int $id): Wallet
    {
        return $this->walletRepository->getById($id);
    }
}
