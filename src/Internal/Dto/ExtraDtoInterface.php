<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

interface ExtraDtoInterface
{
    public function getDepositExtra(): OptionDtoInterface;

    public function getWithdrawExtra(): OptionDtoInterface;
}
