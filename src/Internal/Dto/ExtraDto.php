<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\External\ExtraDtoInterface;
use Bavix\Wallet\External\OptionDtoInterface;

/** @internal */
final class ExtraDto implements ExtraDtoInterface
{
    public function __construct(
        private OptionDtoInterface $deposit,
        private OptionDtoInterface $withdraw
    ) {
    }

    public function getDepositExtra(): OptionDtoInterface
    {
        return $this->deposit;
    }

    public function getWithdrawExtra(): OptionDtoInterface
    {
        return $this->withdraw;
    }
}
