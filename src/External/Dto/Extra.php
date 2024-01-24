<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Dto;

use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\External\Contracts\OptionDtoInterface;

final class Extra implements ExtraDtoInterface
{
    private readonly OptionDtoInterface $deposit;

    private readonly OptionDtoInterface $withdraw;

    /**
     * @param OptionDtoInterface|array<mixed>|null $deposit
     * @param OptionDtoInterface|array<mixed>|null $withdraw
     * @param array<mixed>|null $extra
     */
    public function __construct(
        OptionDtoInterface|array|null $deposit,
        OptionDtoInterface|array|null $withdraw,
        private readonly ?string $uuid = null,
        private readonly ?array $extra = null
    ) {
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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return array<mixed>|null
     */
    public function getExtra(): ?array
    {
        return $this->extra;
    }
}
