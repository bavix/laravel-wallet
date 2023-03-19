<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

class ItemMeta extends Item
{
    public function getTable(): string
    {
        return 'items';
    }

    /**
     * @return array{name: string, price: int}
     */
    public function getMetaProduct(): ?array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
        ];
    }
}
