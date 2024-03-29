<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Dto;

use Bavix\Wallet\External\Contracts\OptionDtoInterface;

final readonly class Option implements OptionDtoInterface
{
    /**
     * @param null|array<mixed> $meta
     */
    public function __construct(
        private ?array $meta,
        private bool $confirmed = true,
        private ?string $uuid = null
    ) {
    }

    /**
     * @return null|array<mixed>
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }
}
