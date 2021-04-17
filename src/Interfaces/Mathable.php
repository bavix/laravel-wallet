<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Mathable
{
    public function add(string $first, string $second, ?int $scale = null): string;

    public function sub(string $first, string $second, ?int $scale = null): string;

    public function div(string $first, string $second, ?int $scale = null): string;

    public function mul(string $first, string $second, ?int $scale = null): string;

    public function pow(string $first, string $second, ?int $scale = null): string;

    public function round(string $number, int $precision = 0): string;

    public function floor(string $number): string;

    public function ceil(string $number): string;

    public function abs(string $number): string;

    public function negative(string $number): string;

    public function compare(string $first, string $second): int;
}
