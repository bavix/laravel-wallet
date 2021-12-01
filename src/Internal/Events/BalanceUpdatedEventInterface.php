<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

interface BalanceUpdatedEventInterface extends EventInterface
{
    public function getWalletId(): int;

    public function getWalletUuid(): string;

    public function getBalance(): string;
}
