<?php

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Ramsey\Uuid\Uuid;

class Operation
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var null|array
     */
    protected $meta;

    /**
     * @var bool
     */
    protected $confirmed;

    /**
     * Transaction constructor.
     * @throws
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return array|null
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    /**
     * @param string $type
     * @return static
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param int $amount
     * @return static
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param array|null $meta
     * @return static
     */
    public function setMeta(?array $meta): self
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @param bool $confirmed
     * @return static
     */
    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;
        return $this;
    }

    /**
     * @param Wallet $wallet
     * @return Transaction
     */
    public function create(Wallet $wallet): Transaction
    {
        /**
         * @var Transaction $model
         */
        $model = $wallet->transactions()->create([
            'type' => $this->getType(),
            'wallet_id' => $wallet->getKey(),
            'uuid' => $this->getUuid(),
            'confirmed' => $this->isConfirmed(),
            'amount' => $this->getAmount(),
            'meta' => $this->getMeta(),
        ]);

        return $model;
    }

}
