<?php

namespace Bavix\Wallet\Test\Objects;

class Operation extends \Bavix\Wallet\Objects\Operation
{

    /**
     * @return array
     */
    public function toArray(): array
    {
        return \array_merge(parent::toArray(), [
            'bank_method' => $this->meta['bank_method'] ?? null,
        ]);
    }

}
