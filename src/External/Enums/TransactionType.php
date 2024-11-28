<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Enums;

use Bavix\Wallet\Models\Transaction;

enum TransactionType: string
{
    case Deposit = Transaction::TYPE_DEPOSIT;
    case Withdraw = Transaction::TYPE_WITHDRAW;
}
