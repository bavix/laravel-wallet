<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

final class BalanceUpdatedEvent implements BalanceUpdatedEventInterface
{
    private int $walletId;
    private string $walletUuid;
    private string $balance;

    public function __construct(
        int $walletId,
        string $walletUuid,
        string $balance
    ) {
        $this->walletId = $walletId;
        $this->walletUuid = $walletUuid;
        $this->balance = $balance;
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function getWalletUuid(): string
    {
        return $this->walletUuid;
    }

    public function getBalance(): string
    {
        return $this->balance;
    }
}
