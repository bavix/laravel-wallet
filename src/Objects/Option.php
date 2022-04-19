<?php

declare(strict_types=1);

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Internal\Dto\OptionDtoInterface;

final class Option implements OptionDtoInterface
{
    public function __construct(
        private ?array $meta = null
    ) {
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }
}
