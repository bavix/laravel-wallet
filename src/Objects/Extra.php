<?php

declare(strict_types=1);

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Internal\Dto\ExtraDtoInterface;
use Bavix\Wallet\Internal\Dto\OptionDtoInterface;

final class Extra implements ExtraDtoInterface
{
    private OptionDtoInterface $deposit;
    private OptionDtoInterface $withdraw;

    public function __construct(array|OptionDtoInterface|null $deposit, array|OptionDtoInterface|null $withdraw)
    {
        $this->deposit = new Option($deposit);
        $this->withdraw = new Option($withdraw);
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
