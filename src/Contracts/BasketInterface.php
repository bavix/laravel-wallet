<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

use Bavix\Wallet\Dto\BasketDto;
use Bavix\Wallet\Models\Transfer;

interface BasketInterface
{
    /**
     * @return Transfer[]
     */
    public function payCart(BasketDto $basketDto, bool $force = false): array;

    /**
     * @return Transfer[]
     */
    public function safePayCart(BasketDto $basketDto, bool $force = false): array;

    /**
     * @return Transfer[]
     */
    public function forcePayCart(BasketDto $basketDto): array;

    public function refundCart(BasketDto $basketDto, bool $force = false, bool $gifts = false): bool;

    public function safeRefundCart(BasketDto $basketDto, bool $force = false, bool $gifts = false): bool;

    public function forceRefundCart(BasketDto $basketDto, bool $gifts = false): bool;
}
