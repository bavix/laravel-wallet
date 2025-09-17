<?php

declare(strict_types=1);

namespace Bavix\Wallet\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdraw = 'withdraw';
}
