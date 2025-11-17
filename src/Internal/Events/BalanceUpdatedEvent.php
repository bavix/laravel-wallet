<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use DateTimeImmutable;

final readonly class BalanceUpdatedEvent implements BalanceUpdatedEventInterface
{
    public function __construct(
        private int $walletId,
        private string $walletUuid,
        private string $balance,
        private DateTimeImmutable $updatedAt
    ) {
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }

    /**
     * @return non-empty-string
     */
    public function getWalletUuid(): string
    {
        /** @var non-empty-string $walletUuid */
        $walletUuid = $this->walletUuid;

        return $walletUuid;
    }

    /**
     * @return non-empty-string
     */
    public function getBalance(): string
    {
        /** @var non-empty-string $balance */
        $balance = $this->balance;

        return $balance;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
