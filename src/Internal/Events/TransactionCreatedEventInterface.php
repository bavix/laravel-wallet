<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use Bavix\Wallet\Enums\TransactionType;
use DateTimeImmutable;

interface TransactionCreatedEventInterface extends EventInterface
{
    public function getId(): int;

    public function getType(): TransactionType;

    public function getWalletId(): int;

    public function getCreatedAt(): DateTimeImmutable;
}
