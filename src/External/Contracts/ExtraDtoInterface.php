<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Contracts;

interface ExtraDtoInterface
{
    public function getDepositOption(): OptionDtoInterface;

    public function getWithdrawOption(): OptionDtoInterface;

    public function getUuid(): ?string;

    /**
     * @return array<mixed>|null
     */
    public function getExtra(): ?array;
}
