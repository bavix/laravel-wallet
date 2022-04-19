<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\External\OptionDtoInterface;

/** @internal */
final class OptionDto implements OptionDtoInterface
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
