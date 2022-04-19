<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Dto;

use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\External\Contracts\OptionDtoInterface;

final class Extra implements ExtraDtoInterface
{
    private OptionDtoInterface $deposit;
    private OptionDtoInterface $withdraw;

    public function __construct(array|OptionDtoInterface|null $deposit, array|OptionDtoInterface|null $withdraw)
    {
        $this->deposit = $deposit instanceof OptionDtoInterface ? $deposit : new Option($deposit);
        $this->withdraw = $withdraw instanceof OptionDtoInterface ? $withdraw : new Option($withdraw);
    }

    public function getDepositOption(): OptionDtoInterface
    {
        return $this->deposit;
    }

    public function getWithdrawOption(): OptionDtoInterface
    {
        return $this->withdraw;
    }
}
