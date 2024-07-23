<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use DateTimeImmutable;

interface TransactionCreatedEventInterface extends EventInterface
{
    /**
     * Returns the ID of the transaction.
     *
     * @return int The transaction ID.
     */
    public function getId(): int;

    /**
     * Returns the type of the transaction.
     *
     * @return string The transaction type.
     */
    public function getType(): string;

    /**
     * Returns the ID of the wallet associated with the transaction.
     *
     * @return int The wallet ID.
     */
    public function getWalletId(): int;

    /**
     * Returns the creation date and time of the transaction.
     *
     * @return DateTimeImmutable The creation date and time.
     */
    public function getCreatedAt(): DateTimeImmutable;
}
