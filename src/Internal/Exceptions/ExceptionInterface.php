<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Exceptions;

use Throwable;

interface ExceptionInterface extends Throwable
{
    public const AMOUNT_INVALID = 1 << 0;
    public const BALANCE_IS_EMPTY = 1 << 1;
    public const CONFIRMED_INVALID = 1 << 2;
    public const UNCONFIRMED_INVALID = 1 << 3;
    public const INSUFFICIENT_FUNDS = 1 << 4;
    public const PRODUCT_ENDED = 1 << 5;
    public const WALLET_OWNER_INVALID = 1 << 6;
    public const CART_EMPTY = 1 << 7;
    public const LOCK_PROVIDER_NOT_FOUND = 1 << 8;
    public const RECORD_NOT_FOUND = 1 << 9;
    public const TRANSACTION_FAILED = 1 << 10;
    public const MODEL_NOT_FOUND = 1 << 11;
    public const UNKNOWN_EVENT = 1 << 12;
}
