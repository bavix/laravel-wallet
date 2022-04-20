<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

interface TransferServiceInterface
{
    /**
     * @param int[] $ids
     */
    public function updateStatusByIds(string $status, array $ids): bool;
}
