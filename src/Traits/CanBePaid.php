<?php

namespace Bavix\Wallet\Traits;

/**
 * Trait CanBePaid
 * @package Bavix\Wallet\Traits
 * @deprecated use trait CanPay
 * @see https://github.com/bavix/laravel-wallet/issues/19
 */
trait CanBePaid
{
    use CanPay;
}
