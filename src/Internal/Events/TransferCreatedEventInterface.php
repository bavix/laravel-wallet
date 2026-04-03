<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use Bavix\Wallet\Enums\TransferStatus;
use DateTimeImmutable;

interface TransferCreatedEventInterface extends EventInterface
{
    public function getTransferId(): int;

    public function getFromWalletId(): int;

    public function getToWalletId(): int;

    public function getStatus(): TransferStatus;

    public function getCreatedAt(): DateTimeImmutable;
}
