<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Models\Transfer;

/**
 * @api
 */
interface PurchaseQueryHandlerInterface
{
    /**
     * @param non-empty-array<PurchaseQueryInterface> $objects
     * @return array<int, Transfer|null>
     */
    public function apply(array $objects): array;

    public function one(PurchaseQueryInterface $query): ?Transfer;
}
