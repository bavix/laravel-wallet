<?php

declare(strict_types=1);

namespace Bavix\Wallet\External;

interface ExtraDtoInterface
{
    public function getDepositExtra(): OptionDtoInterface;

    public function getWithdrawExtra(): OptionDtoInterface;
}
