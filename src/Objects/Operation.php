<?php

namespace Bavix\Wallet\Objects;

use Bavix\Wallet\Internal\MathInterface;

/** @deprecated There is no alternative yet, but the class will be removed */
class Operation
{
    private string $type;

    private string $amount;

    private ?array $meta;

    private bool $confirmed;

    private MathInterface $math;

    /**
     * Transaction constructor.
     */
    public function __construct(
        MathInterface $math
    ) {
        $this->math = $math;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): string
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
}
