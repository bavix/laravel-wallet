<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use DateTimeImmutable;

interface BalanceUpdatedEventInterface extends EventInterface
{
    /**
     * Returns the ID of the wallet that was updated.
     *
     * @return int The ID of the wallet.
     */
    public function getWalletId(): int;

    /**
     * Returns the UUID of the wallet that was updated.
     *
     * @return string The UUID of the wallet.
     */
    public function getWalletUuid(): string;

    /**
     * Returns the balance of the wallet after the update.
     *
     * @return string The balance of the wallet.
     */
    public function getBalance(): string;

    /**
     * Returns the date and time of the update.
     *
     * @return DateTimeImmutable The date and time of the update.
     */
    public function getUpdatedAt(): DateTimeImmutable;
}
