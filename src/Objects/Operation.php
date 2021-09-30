<?php

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\UuidInterface;
use Bavix\Wallet\Models\Transaction;

/** @deprecated There is no alternative yet, but the class will be removed */
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
     * @var Wallet
     */
    protected $wallet;

    /**
     * Transaction constructor.
     *
     * @throws
     */
    public function __construct(UuidInterface $uuidService)
    {
        $this->uuid = $uuidService->uuid4();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return float|int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    /**
     * @return static
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param int|string $amount
     *
     * @return static
     */
    public function setAmount($amount): self
    {
        $this->amount = app(MathInterface::class)->round($amount);

        return $this;
    }

    /**
     * @return static
     */
    public function setMeta(?array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @return static
     */
    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    /**
     * @return static
     */
    public function setWallet(Wallet $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function create(): Transaction
    {
        /**
         * @var Transaction $model
         */
        return $this->getWallet()
            ->transactions()
            ->create($this->toArray())
        ;
    }

    /**
     * @throws
     */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'wallet_id' => $this->getWallet()->getKey(),
            'uuid' => $this->getUuid(),
            'confirmed' => $this->isConfirmed(),
            'amount' => $this->getAmount(),
            'meta' => $this->getMeta(),
        ];
    }
}
