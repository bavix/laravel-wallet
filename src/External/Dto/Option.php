<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Dto;

use Bavix\Wallet\External\Contracts\OptionDtoInterface;

final class Option implements OptionDtoInterface
{
    public function __construct(
        private ?array $meta,
        private bool $confirmed = true
    ) {
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }
}
