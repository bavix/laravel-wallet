<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

interface StateAwareTransactionDtoInterface extends TransactionDtoInterface
{
    /**
     * Balance before current transaction apply.
     */
    public function getBalanceBefore(): string;

    /**
     * Balance after current transaction apply.
     */
    public function getBalanceAfter(): string;
}
