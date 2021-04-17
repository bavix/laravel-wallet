<?php

namespace Bavix\Wallet\Test\Models;

class ItemMeta extends Item
{
    public function getTable(): string
    {
        return 'items';
    }

    public function getMetaProduct(): ?array
    {
        return ['name' => $this->name, 'price' => $this->price];
    }
}
