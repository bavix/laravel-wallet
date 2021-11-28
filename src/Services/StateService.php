<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Wallet;

final class StateService implements StateServiceInterface
{
    private BookkeeperServiceInterface $bookkeeperService;
    private RegulatorServiceInterface $regulatorService;
    private MathServiceInterface $mathService;

    /** @var Wallet[] */
    private array $wallets = [];

    public function __construct(
        BookkeeperServiceInterface $bookkeeperService,
        RegulatorServiceInterface $regulatorService,
        MathServiceInterface $mathService
    ) {
        $this->bookkeeperService = $bookkeeperService;
        $this->regulatorService = $regulatorService;
        $this->mathService = $mathService;
    }

    public function persist(Wallet $wallet): void
    {
        $this->wallets[] = $wallet;
    }

    public function commit(): void
    {
        $flags = [];
        foreach ($this->wallets as $wallet) {
            if ($flags[$wallet->uuid] ?? false) {
                continue;
            }

            $diffValue = $this->regulatorService->diff($wallet);
            if ($this->mathService->compare($diffValue, 0) === 0) {
                continue;
            }

            $this->bookkeeperService->increase($wallet, $diffValue);
            $flags[$wallet->uuid] = true;
        }

        $this->purge();
    }

    public function purge(): void
    {
        foreach ($this->wallets as $wallet) {
            $this->regulatorService->missing($wallet);
        }

        $this->wallets = [];
    }
}
