<?php

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\Service\CastService;
use Bavix\Wallet\Internal\UuidInterface;
use Bavix\Wallet\Models\Transaction;
use DateTimeImmutable;

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
     * @var string
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

    private CastService $castService;

    private MathInterface $math;

    /**
     * Transaction constructor.
     */
    public function __construct(
        UuidInterface $uuidService,
        CastService $castService,
        MathInterface $math
    ) {
        $this->uuid = $uuidService->uuid4();
        $this->castService = $castService;
        $this->math = $math;
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
     * @return string
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
        $this->amount = $this->math->round($amount);

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

    public function toArray(): array
    {
        $wallet = $this->castService->getWallet($this->getWallet());
        $payable = $this->castService->getHolder($wallet);
        $meta = $this->getMeta();

        return [
            'type' => $this->getType(),
            'payable_type' => $payable->getMorphClass(),
            'payable_id' => $payable->getKey(),
            'wallet_id' => $wallet->getKey(),
            'uuid' => $this->getUuid(),
            'confirmed' => $this->isConfirmed(),
            'amount' => $this->getAmount(),
            'meta' => $meta === null ? null : json_encode($meta, JSON_THROW_ON_ERROR),
            'created_at' => new DateTimeImmutable(),
            'updated_at' => new DateTimeImmutable(),
        ];
    }
}
