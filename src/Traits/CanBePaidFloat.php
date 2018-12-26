<?php

namespace Bavix\Wallet\Traits;

/**
 * Trait CanBePaidFloat
 * @package Bavix\Wallet\Traits
 * @deprecated use trait CanPayFloat
 * @see https://github.com/bavix/laravel-wallet/issues/19
 */
trait CanBePaidFloat
{
    use CanPayFloat;
}
