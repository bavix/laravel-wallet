<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface MathServiceInterface
{
    public function add(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    public function sub(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    public function div(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    public function mul(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    public function pow(float|int|string $first, float|int|string $second, ?int $scale = null): string;

    public function powTen(float|int|string $number): string;

    public function round(float|int|string $number, int $precision = 0): string;

    public function floor(float|int|string $number): string;

    public function ceil(float|int|string $number): string;

    public function abs(float|int|string $number): string;

    public function negative(float|int|string $number): string;

    public function compare(float|int|string $first, float|int|string $second): int;
}
