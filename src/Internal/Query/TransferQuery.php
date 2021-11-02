<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Query;

class TransferQuery
{
    private array $uuids;

    /** @param string[] $uuids */
    public function __construct(array $uuids)
    {
        $this->uuids = $uuids;
    }

    /** @return string[] */
    public function getUuids(): array
    {
        return $this->uuids;
    }
}
