<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use DateTimeImmutable;

interface WalletCreatedEventInterface extends EventInterface
{
    /**
     * Returns the type of the holder.
     */
    public function getHolderType(): string;

    /**
     * Returns the ID of the holder.
     */
    public function getHolderId(): int|string;

    /**
     * Returns the ID of the wallet.
     */
    public function getWalletId(): int;

    /**
     * Returns the UUID of the wallet.
     */
    public function getWalletUuid(): string;

    /**
     * Returns the creation date of the wallet.
     */
    public function getCreatedAt(): DateTimeImmutable;
}
