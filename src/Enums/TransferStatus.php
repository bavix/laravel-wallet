<?php

declare(strict_types=1);

namespace Bavix\Wallet\Enums;

enum TransferStatus: string
{
    case Exchange = 'exchange';
    case Transfer = 'transfer';
    case Paid = 'paid';
    case Refund = 'refund';
    case Gift = 'gift';
}
